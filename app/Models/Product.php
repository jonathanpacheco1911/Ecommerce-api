<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Camiseta Deportiva"),
 *     @OA\Property(property="description", type="string", example="Camiseta transpirable talla M"),
 *     @OA\Property(property="price", type="number", format="float", example=29.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="sku", type="string", example="CAM-DEP-001"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/img.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'sku',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Verifica si hay stock suficiente.
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }
}
