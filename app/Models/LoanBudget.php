<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanBudget extends Model
{
    protected $table = 'loan_budgets';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    /** Set particular field mass assignable */
    // protected $fillable = [];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }
}
