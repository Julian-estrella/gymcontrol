<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    // Role constants
    const ROLE_ADMIN   = 'admin';
    const ROLE_STAFF   = 'staff';
    const ROLE_CLIENTE = 'cliente';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'password'          => 'hashed',
        ];
    }

    /**
     * Role Helpers
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isCliente(): bool
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function roleLabel(): string
    {
        if ($this->isAdmin()) {
            return 'Admin';
        }

        if ($this->isStaff()) {
            return 'Staff';
        }

        if ($this->isCliente()) {
            return 'Cliente';
        }

        return $this->roleRecord?->name ?? ucfirst((string) $this->role);
    }

    public function canAccessAdmin(): bool
    {
        if ($this->isAdmin() || $this->isStaff()) {
            return true;
        }

        return (bool) $this->roleRecord?->can_access_admin;
    }

    public function canAccessModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isStaff()) {
            return in_array($module, ['clients', 'trainers', 'classes', 'membership-plans', 'payments'], true);
        }

        $role = $this->roleRecord;

        return (bool) ($role?->can_access_admin && $role->hasModule($module));
    }

    /**
     * Get the dashboard route based on the user's role.
     */
    public function dashboardRoute(): string
    {
        return $this->canAccessAdmin() ? 'admin.dashboard' : 'cliente.dashboard';
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role === self::ROLE_CLIENTE) {
                $client = Client::where('email', $user->email)->first();
                if ($client) {
                    $client->updateQuietly(['user_id' => $user->id]);
                } else {
                    $user->client()->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'membership_status' => 'sin_membresia',
                    ]);
                }
            }
        });

        static::updated(function ($user) {
            if ($user->role === self::ROLE_CLIENTE) {
                if ($user->client) {
                    $user->client->updateQuietly([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ]);
                } else {
                    $user->client()->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'membership_status' => 'sin_membresia',
                    ]);
                }
            }
        });
    }
}
