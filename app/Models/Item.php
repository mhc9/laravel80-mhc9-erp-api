<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    /** Set particular field mass assignable */
    // protected $fillable = ['name','category_id','description','cost','price','unit_id','img_url','status'];

    /** Set all the fields mass assignable */
    protected $guarded = [];


    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
