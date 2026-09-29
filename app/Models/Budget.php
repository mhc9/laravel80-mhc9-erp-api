<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'budgets';

    // protected $primaryKey = 'id';

    /** false = ไม่ใช้ options auto increment */
    // public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Append the custom attribute */
    protected $appends = ['total_expenses'];

    public function activity()
    {
        return $this->belongsTo(BudgetActivity::class, 'activity_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(BudgetType::class, 'budget_type_id', 'id');
    }

    public function expenses()
    {
        return $this->hasMany(BudgetExpense::class, 'budget_id', 'id');
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }
}
