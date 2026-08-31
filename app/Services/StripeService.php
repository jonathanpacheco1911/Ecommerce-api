<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crea un PaymentIntent en Stripe para la orden indicada.
     *
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Order $order): PaymentIntent
    {
        return $this->client->paymentIntents->create([
            'amount' => $this->toStripeAmount((float) $order->total),
            'currency' => $order->currency,
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    /**
     * Recupera un PaymentIntent existente desde Stripe (para confirmar su estado real).
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Convierte un monto decimal (ej. 29.99) al formato de centavos que espera Stripe (2999).
     */
    protected function toStripeAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
