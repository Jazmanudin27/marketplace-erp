<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'shop_id',
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
}
