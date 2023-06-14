<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetOwnerShip extends Model
{
    protected $table = 'asset_ownerships';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(Employee::class, 'owner_id', 'id');
    }
}
