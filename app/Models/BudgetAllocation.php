<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetAllocation extends Model
{
    protected $table = 'budget_allocations';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    protected $fillable = ['status'];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'id');
    }
}
