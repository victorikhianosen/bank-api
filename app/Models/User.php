<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'roles'
    ];

    protected $appends = ['role_name'];

    public function getRoleNameAttribute()
    {
        return $this->getRoleNames()->first();
    }


    protected $guard_name = 'user';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function accountOfficers()
    {
        return $this->hasMany(AccountOfficer::class);
    }
}
