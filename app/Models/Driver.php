<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function member_of()
    {
        return $this->belongsTo(VehicleOwner::class, 'member_of', 'id');
    }

    public function assignments()
    {
        return $this->hasMany(ReservationAssignment::class, 'driver_id', 'id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id', 'id');
    }
}
