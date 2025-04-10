<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanContractDetail extends Model
{
    protected $table = 'loan_contract_details';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    /** Set particular field mass assignable */
    // protected $fillable = [];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(LoanContract::class, 'contract_id', 'id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id', 'id');
    }

    public function loanDetail()
    {
        return $this->belongsTo(LoanDetail::class, 'loan_detail_id', 'id');
    }
}
