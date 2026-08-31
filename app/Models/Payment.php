<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="stripe_payment_intent_id", type="string", example="pi_3Nabc123XYZ"),
 *     @OA\Property(property="stripe_client_secret", type="string", example="pi_3Nabc123XYZ_secret_abc"),
 *     @OA\Property(property="amount", type="number", format="float", example=59.98),
 *     @OA\Property(property="currency", type="string", example="usd"),
 *     @OA\Property(property="status", type="string", enum={"requires_payment_method","requires_confirmation","processing","succeeded","canceled","failed"}, example="succeeded"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class Payment extends Model
{
    use HasFactory;

    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'order_id',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'amount',
        'currency',
        'status',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
