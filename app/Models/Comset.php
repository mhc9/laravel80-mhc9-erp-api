<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comset extends Model
{
    protected $table = 'comsets';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(AssetGroup::class, 'asset_group_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(AssetGroup::class, 'asset_group_id', 'id');
    }
}
