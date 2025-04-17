<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRefundBudget extends Model
{
    protected $table = 'loan_refund_budgets';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function refund()
    {
        return $this->belongsTo(LoanRefund::class, 'refund_id', 'id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }
}
