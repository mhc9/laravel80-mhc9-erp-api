<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'budgets';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function activity()
    {
        return $this->belongsTo(BudgetActivity::class, 'activity_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(BudgetType::class, 'budget_type_id', 'id');
    }
}
