<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $table = 'places';
    // protected $primaryKey = 'id';
    // public $incrementing = false; // false = ไม่ใช้ options auto increment
    // public $timestamps = false; // false = ไม่ใช้ field updated_at และ created_at

    /** Set particular field mass assignable */
    // protected $fillable = ['name','place_type_id','address_no','road','moo','tambon_id','amphur_id','changwat_id','zipcode','latitude','longitude','status'];

    /** Set all the fields mass assignable */
    protected $guarded = [];

    public function tambon()
    {
        return $this->belongsTo(Tambon::class, 'tambon_id', 'id');
    }

    public function amphur()
    {
        return $this->belongsTo(Amphur::class, 'amphur_id', 'id');
    }

    public function changwat()
    {
        return $this->belongsTo(Changwat::class, 'changwat_id', 'id');
    }
}
