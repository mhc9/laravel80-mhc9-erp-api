<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpmDepartment extends Model
{
    protected $connection = "sqlsrv";
    protected $table = 'Section';
    protected $primaryKey = 'SeId';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at
}
