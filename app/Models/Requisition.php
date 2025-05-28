<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';

    // protected $primaryKey = 'id';

    /** false = ไม่ใช้ options auto increment */
    // public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Set particular field mass assignable */
    // protected $fillable = ['status'];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id', 'id');
    }

    /** TODO: remove */
    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id', 'id');
    }

    public function deputy()
    {
        return $this->belongsTo(Employee::class, 'deputy_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(RequisitionDetail::class, 'requisition_id', 'id');
    }

    public function committees()
    {
        return $this->hasMany(Committee::class, 'requisition_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'requisition_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(RequisitionBudget::class, 'requisition_id', 'id');
    }
}
