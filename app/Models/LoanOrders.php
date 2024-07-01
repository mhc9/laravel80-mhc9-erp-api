<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanOrder extends Model
{
    protected $table = 'loan_orders';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id', 'id');
    }
}
