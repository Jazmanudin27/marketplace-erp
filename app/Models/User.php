<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('role_id', 'status')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'company_user')
            ->withPivot('company_id', 'status')
            ->withTimestamps();
    }

    public function hasRole(string $roleName, ?int $companyId = null): bool
    {
        if ($companyId === null) {
            $companyId = $this->company_id;
        }

        return $this->roles()
            ->where('name', $roleName)
            ->where('company_user.company_id', $companyId)
            ->exists();
    }
}
