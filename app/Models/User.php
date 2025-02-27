<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{

    use HasRoles; // 🔹 Necesario para manejar roles y permisos


    protected function password(): Attribute
{
    return Attribute::make(
        set: fn ($value) => bcrypt($value),
    );
}




    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    protected static function boot()
{
    parent::boot();

    static::created(function ($user) {
       

        // Verifica si hay un rol en los datos de creación
        if (request()->has('role')) {
            $user->assignRole(request()->input('role'));
            file_put_contents(storage_path('logs/test_log.txt'), "Rol asignado: " . request()->input('role') . "\n", FILE_APPEND);
        } else {
            $user->assignRole('Cobrador'); // Asigna "Cobrador" por defecto
            file_put_contents(storage_path('logs/test_log.txt'), "Rol predeterminado asignado: Cobrador\n", FILE_APPEND);
        }
    });
}
   
}
