<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function type()
    {
        return $this->belongsTo(ReservationType::class, 'type_id', 'id');
    }

    public function assignments()
    {
        return $this->hasMany(ReservationAssignment::class, 'reservation_id', 'id');
    }
}
