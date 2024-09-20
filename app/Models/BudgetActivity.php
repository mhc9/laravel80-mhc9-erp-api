<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetActivity extends Model
{
    protected $table = 'budget_activities';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function project()
    {
        return $this->belongsTo(BudgetProject::class, 'project_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'activity_id', 'id');
    }
}
