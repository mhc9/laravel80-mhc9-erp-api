<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetTypeDetail extends Model
{
    protected $table = 'budget_type_details';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(BudgetType::class, 'budget_type_id', 'id');
    }
}
