<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetExpenseType extends Model
{
    protected $table = 'budget_expense_types';

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(BudgetType::class, 'budget_type_id', 'id');
    }
}
