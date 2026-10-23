<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'role_id',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Role relationship.
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Dynamic permission checker.
     * Administrator always passes all permissions.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = $this->roleModel;
        if (!$role && $this->role) {
            $role = Role::where('slug', $this->role)->first();
        }

        return $role ? $role->hasPermission($permissionSlug) : false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || ($this->roleModel && $this->roleModel->slug === 'admin');
    }

    public function isOperasional(): bool
    {
        return $this->isAdmin() || $this->hasPermission('worksheets.create') || $this->role === 'operasional';
    }

    public function isKeuangan(): bool
    {
        return $this->isAdmin() || $this->hasPermission('invoices.view') || $this->role === 'keuangan';
    }
}
