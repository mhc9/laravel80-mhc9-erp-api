<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRefundDetail extends Model
{
    protected $table = 'loan_refund_details';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function refund()
    {
        return $this->belongsTo(LoanRefund::class, 'refund_id', 'id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id', 'id');
    }

    public function contractDetail()
    {
        return $this->belongsTo(LoanContractDetail::class, 'contract_detail_id', 'id');
    }
}
