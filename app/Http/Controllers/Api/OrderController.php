<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Órdenes"},
     *     summary="Historial de compras del cliente autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de órdenes del cliente",
     *         @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")))
     *     ),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index()
    {
        $orders = request()->user()
            ->orders()
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->success($orders, 'Historial de compras obtenido');
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     tags={"Órdenes"},
     *     summary="Ver el detalle de una orden propia",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle de la orden", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Order"))),
     *     @OA\Response(response=403, description="No autorizado para ver esta orden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Orden no encontrada", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Order $order)
    {
        if ($order->user_id !== request()->user()->id) {
            return $this->error('No tienes permiso para ver esta orden.', 403);
        }

        return $this->success($order->load(['items.product', 'payment']), 'Detalle de la orden');
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Órdenes"},
     *     summary="Crear una nueva orden de compra",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id","quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Orden creada exitosamente", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Order"))),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Error de validación o stock insuficiente", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $productIds = collect($request->validated('items'))->pluck('product_id');

                // Bloquea las filas de productos involucradas para evitar condiciones de carrera sobre el stock.
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                $total = 0;
                $itemsData = [];

                foreach ($request->validated('items') as $item) {
                    $product = $products->get($item['product_id']);

                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException("El producto con ID {$item['product_id']} no está disponible.");
                    }

                    if (! $product->hasStock($item['quantity'])) {
                        throw new \RuntimeException("Stock insuficiente para el producto '{$product->name}'. Disponible: {$product->stock}.");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    $itemsData[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ];
                }

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'status' => Order::STATUS_PENDING,
                    'total' => $total,
                    'currency' => 'usd',
                ]);

                foreach ($itemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['product']->id,
                        'quantity' => $data['quantity'],
                        'unit_price' => $data['unit_price'],
                        'subtotal' => $data['subtotal'],
                    ]);

                    // Reserva el stock inmediatamente al crear la orden.
                    $data['product']->decrement('stock', $data['quantity']);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($order->load('items.product'), 'Orden creada exitosamente. Procede al pago.', 201);
    }
}
