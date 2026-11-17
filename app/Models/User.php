<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasRoles;

    protected $fillable = [
        'name', 'email', 'password',"phone","company_name", "role_id", "biller_id", "warehouse_id", "kitchen_id", "service_staff", "is_active", "is_deleted"
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if (! $user->role_id || ! \Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                return;
            }

            DB::table('model_has_roles')
                ->where('model_type', static::class)
                ->where('model_id', $user->id)
                ->delete();

            DB::table('model_has_roles')->insert([
                'role_id' => $user->role_id,
                'model_type' => static::class,
                'model_id' => $user->id,
            ]);
        });
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function holiday() {
        return $this->hasMany('App\Models\Holiday');
    }
}
