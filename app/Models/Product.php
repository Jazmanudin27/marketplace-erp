<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'marketplace_product_id',
        'marketplace',
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'image_url',
        'category',
        'status',
        'marketplace_data',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'marketplace_data' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
