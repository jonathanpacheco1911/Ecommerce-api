<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Traits\ApiResponser;

class ProductController extends Controller
{
    use ApiResponser;

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Productos"},
     *     summary="Listar catálogo público de productos activos",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="search", in="query", description="Buscar por nombre", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de productos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $query = Product::query()->where('is_active', true);

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate((int) request('per_page', 15));

        return $this->success($products, 'Listado de productos');
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}",
     *     tags={"Productos"},
     *     summary="Obtener el detalle de un producto",
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle del producto", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Product"))),
     *     @OA\Response(response=404, description="Producto no encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Product $product)
    {
        return $this->success($product, 'Producto encontrado');
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Productos"},
     *     summary="Crear un nuevo producto (requiere autenticación)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock","sku"},
     *             @OA\Property(property="name", type="string", example="Camiseta Deportiva"),
     *             @OA\Property(property="description", type="string", example="Camiseta transpirable talla M"),
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="sku", type="string", example="CAM-DEP-001"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/img.jpg"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Producto creado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Product"))),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->success($product, 'Producto creado exitosamente', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     tags={"Productos"},
     *     summary="Actualizar un producto existente (requiere autenticación)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Camiseta Deportiva Pro"),
     *             @OA\Property(property="price", type="number", format="float", example=34.99),
     *             @OA\Property(property="stock", type="integer", example=80)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Producto actualizado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Product"))),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Producto no encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return $this->success($product->fresh(), 'Producto actualizado exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     tags={"Productos"},
     *     summary="Eliminar un producto (requiere autenticación)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Producto eliminado exitosamente"),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Producto no encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->success(null, 'Producto eliminado exitosamente');
    }
}
