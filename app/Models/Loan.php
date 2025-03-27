<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'loans';

    // protected $primaryKey = 'id';

    /** false = ไม่ใช้ options auto increment */
    // public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Set particular field mass assignable */
    // protected $fillable = ['status'];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(LoanDetail::class, 'loan_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(LoanBudget::class, 'loan_id', 'id');
    }

    public function courses()
    {
        return $this->hasMany(ProjectCourse::class, 'loan_id', 'id')->orderBy('course_date');
    }
}
