<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'shop_id',
        'shop_cipher',
        'shop_name',
        'access_token',
        'refresh_token',
        'expired_at',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(MarketplaceProduct::class, 'marketplace_account_id');
    }
}
