<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComsetEquipment extends Model
{
    protected $table = 'comset_equipments';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
