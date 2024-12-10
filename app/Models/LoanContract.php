<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanContract extends Model
{
    protected $table = 'loan_contracts';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    protected $fillable = ['refund_notify','status'];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }

    public function refund()
    {
        return $this->hasOne(LoanRefund::class, 'contract_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(LoanContractDetail::class, 'contract_id', 'id');
    }
}
