<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comset extends Model
{
    protected $table = 'comsets';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function equipments()
    {
        return $this->hasMany(ComsetEquipment::class, 'comset_id', 'id')->orderBy('equipment_type_id');
    }

    public function assets()
    {
        return $this->hasMany(ComsetAsset::class, 'asset_id', 'id')->orderBy('asset_id');
    }
}
