<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location', // Add location field for kassirs
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

    // Role check methods
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isChinaKassir(): bool
    {
        return $this->hasRole('kassir_china');
    }

    public function isUzbKassir(): bool
    {
        return $this->hasRole('kassir_uzb');
    }

    public function isKassir(): bool
    {
        return $this->hasAnyRole(['kassir_china', 'kassir_uzb']);
    }

    public function canImportFromChina(): bool
    {
        return $this->hasPermissionTo('import_china_excel');
    }

    public function canImportFromUzbekistan(): bool
    {
        return $this->hasPermissionTo('import_uzb_excel');
    }

    public function canManageParcels(): bool
    {
        return $this->hasPermissionTo('manage_parcels');
    }

    public function canViewDashboard(): bool
    {
        return $this->hasPermissionTo('view_dashboard');
    }
}
