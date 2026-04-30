<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpmPosition extends Model
{
    protected $connection = "sqlsrv";
    protected $table = 'Position';
    protected $primaryKey = 'PosId';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at
}
