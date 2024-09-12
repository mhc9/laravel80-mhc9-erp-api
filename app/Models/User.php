<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /** MSSQLServer */
    // protected $fillable = [
    //     'UsName',
    //     'UsUser',
    //     'UsPassword',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /** MySQL */
    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
    ];

    /** MSSQLServer */
    // protected $hidden = [
    //     'UsPassword',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    /**
     * MSSQLServer
     */
    // protected $table = 'Users';
    // protected $primaryKey = 'UsId';

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        /** MySQL */
        return $this->password;

        /** MSSQLServer */
        // return $this->UsPassword;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id');
    }

    public function isAdmin()
    {
        return $this->permissions()->where('role_id', 1)->exists();
    }

    public function isFinancial()
    {
        return $this->permissions()->where('role_id', 4)->exists();
    }

    public function isParcel()
    {
        return $this->permissions()->where('role_id', 3)->exists();
    }
}
