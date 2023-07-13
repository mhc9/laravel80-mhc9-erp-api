<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(RequisitionDetail::class, 'pr_id', 'id');
    }
}
