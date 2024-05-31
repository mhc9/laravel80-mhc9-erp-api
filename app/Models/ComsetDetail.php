<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComsetDetail extends Model
{
    protected $table = 'comset_details';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_id', 'id');
    }
}
