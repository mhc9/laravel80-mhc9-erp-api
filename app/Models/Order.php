<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    // protected $primaryKey = 'id';

    /** false = ไม่ใช้ options auto increment */
    // public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Set particular field mass assignable */
    // protected $fillable = ['status'];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'requisition_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
