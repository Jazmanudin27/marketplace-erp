<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'description',
        'phone',
        'website',
        'logo',
        'status',
        'max_users',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('role_id', 'status')
            ->withTimestamps();
    }

    public function marketplaceAccounts(): HasMany
    {
        return $this->hasMany(MarketplaceAccount::class);
    }
}
