<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpmEvent extends Model
{
    protected $connection = "sqlsrv";
    protected $table = 'OT';
    protected $primaryKey = 'OTId';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u'; // กำหนดรูปแบบวันที่ให้ตรงกับฐานข้อมูล SQL Server
    }

    public function employee()
    {
        return $this->belongsTo(WpmEmployee::class, 'OTEmid', 'EmId');
    }
}
