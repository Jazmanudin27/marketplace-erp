<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProduct extends Model
{
    use HasFactory;

    protected $table = 'marketplace_products';

    protected $fillable = [
        'marketplace_account_id',
        'product_id',
        'sku_id',
        'name',
        'seller_sku',
        'price',
        'stock',
        'status',
        'image',
        'raw_data',
        'synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'raw_data' => 'array',
        'synced_at' => 'datetime',
    ];

    public function marketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class);
    }
}
