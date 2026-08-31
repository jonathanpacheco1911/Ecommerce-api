<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StripeService;
use App\Traits\ApiResponser;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    use ApiResponser;

    public function __construct(protected StripeService $stripe)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{order}/pay",
     *     tags={"Pagos"},
     *     summary="Iniciar el proceso de pago de una orden mediante Stripe (crea un PaymentIntent)",
     *     description="Genera un PaymentIntent en Stripe para la orden indicada. El 'client_secret' devuelto debe usarse en el frontend/app móvil con Stripe.js o el SDK de Stripe para confirmar el pago con los datos de la tarjeta.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=201,
     *         description="PaymentIntent creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="payment", ref="#/components/schemas/Payment"),
     *                 @OA\Property(property="client_secret", type="string", example="pi_3Nabc123XYZ_secret_abc")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=409, description="La orden ya fue pagada o no está pendiente", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=502, description="Error al comunicarse con Stripe", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function initiate(Order $order)
    {
        if ($order->user_id !== request()->user()->id) {
            return $this->error('No tienes permiso para pagar esta orden.', 403);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return $this->error('Esta orden no se encuentra en estado pendiente de pago.', 409);
        }

        try {
            $intent = $this->stripe->createPaymentIntent($order);
        } catch (ApiErrorException $e) {
            return $this->error('No se pudo iniciar el pago con Stripe: '.$e->getMessage(), 502);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => $intent->id,
            'stripe_client_secret' => $intent->client_secret,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => $intent->status,
        ]);

        return $this->success([
            'payment' => $payment,
            'client_secret' => $intent->client_secret,
        ], 'Pago iniciado. Confirma la transacción desde el cliente con Stripe.js/SDK.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}/payment/status",
     *     tags={"Pagos"},
     *     summary="Consultar el estado actual del pago de una orden (sincroniza con Stripe)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Estado del pago", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Payment"))),
     *     @OA\Response(response=403, description="No autorizado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="No existe un pago para esta orden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function status(Order $order)
    {
        if ($order->user_id !== request()->user()->id) {
            return $this->error('No tienes permiso para consultar esta orden.', 403);
        }

        $payment = $order->payment;

        if (! $payment) {
            return $this->error('Esta orden aún no tiene un pago asociado.', 404);
        }

        try {
            $intent = $this->stripe->retrievePaymentIntent($payment->stripe_payment_intent_id);
        } catch (ApiErrorException $e) {
            return $this->error('No se pudo consultar el estado en Stripe: '.$e->getMessage(), 502);
        }

        $payment->update(['status' => $intent->status]);

        if ($intent->status === 'succeeded' && $order->status !== Order::STATUS_PAID) {
            $order->update(['status' => Order::STATUS_PAID]);
        } elseif (in_array($intent->status, ['canceled'], true) && $order->status !== Order::STATUS_FAILED) {
            $order->update(['status' => Order::STATUS_FAILED]);
        }

        return $this->success($payment->fresh(), 'Estado del pago actualizado');
    }

    /**
     * @OA\Post(
     *     path="/api/stripe/webhook",
     *     tags={"Pagos"},
     *     summary="Webhook de Stripe para eventos de pago (uso interno de Stripe, sin autenticación de usuario)",
     *     description="Stripe llama a este endpoint para notificar eventos como payment_intent.succeeded o payment_intent.payment_failed. La firma se valida usando STRIPE_WEBHOOK_SECRET.",
     *     @OA\Response(response=200, description="Evento procesado correctamente"),
     *     @OA\Response(response=400, description="Firma de webhook inválida")
     * )
     */
    public function webhook()
    {
        $payload = request()->getContent();
        $sigHeader = request()->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Firma de webhook inválida.'], 400);
        }

        $intent = $event->data->object ?? null;

        if ($intent && isset($intent->id)) {
            $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => $intent->status ?? $payment->status,
                    'raw_response' => $intent->toArray(),
                ]);

                if (($intent->status ?? null) === 'succeeded') {
                    $payment->order->update(['status' => Order::STATUS_PAID]);
                } elseif (($intent->status ?? null) === 'canceled') {
                    $payment->order->update(['status' => Order::STATUS_FAILED]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
