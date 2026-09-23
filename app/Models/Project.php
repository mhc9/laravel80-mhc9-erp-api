<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // TODO: Import the Str facade for UUID

class Project extends Model
{
    protected $table = 'projects';

    /** Set primary key name manually if not id */
    // protected $primaryKey = 'id';
    
    /** TODO: The primary key type is a string */
    protected $keyType = 'string';

    /** TODO:false = ไม่ใช้ options auto increment */
    public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

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

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(Employee::class, 'owner_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }
}
