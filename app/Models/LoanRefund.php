<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRefund extends Model
{
    protected $table = 'loan_refunds';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function contract()
    {
        return $this->belongsTo(LoanContract::class, 'contract_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(LoanRefundDetail::class, 'refund_id', 'id');
    }
}
