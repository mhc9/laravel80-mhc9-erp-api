<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetExpense extends Model
{
    protected $table = 'budget_expenses';

    /** Set primary key name manually if not id */
    // protected $primaryKey = 'id';
    
    /** TODO: The primary key type is a string */
    protected $keyType = 'string';

    /** TODO: false = ไม่ใช้ options auto increment */
    public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Set particular field mass assignable */
    // protected $fillable = ['employee_id'];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    /** TODO: Boot method to set the UUID automatically */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if the UUID is already set (e.g., if manually provided)
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid(); // Generate a new UUID
            }
        });
    }

    public function activity()
    {
        return $this->belongsTo(BudgetActivity::class, 'activity_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(BudgetType::class, 'budget_type_id', 'id');
    }
}
