<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicles';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'type_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(VehicleOwner::class, 'owner_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}
