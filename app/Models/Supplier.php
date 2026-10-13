<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    /** Set primary key name manually if not id */
    // protected $primaryKey = 'id';

    /** false = ไม่ใช้ options auto increment */
    // public $incrementing = false;

    /** false = ไม่ใช้ field updated_at และ created_at */
    // public $timestamps = false;

    /** Append the custom attribute */
    protected $appends = ['full_address'];

    public function changwat()
    {
        return $this->belongsTo(Changwat::class, 'changwat_id', 'id');
    }

    public function amphur()
    {
        return $this->belongsTo(Amphur::class, 'amphur_id', 'id');
    }

    public function tambon()
    {
        return $this->belongsTo(Tambon::class, 'tambon_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }

    public function getFullAddressAttribute()
    {
        $addressParts = [];

        if ($this->address) {
            $addressParts[] = $this->address;
        }

        if ($this->moo) {
            $addressParts[] = 'ม.'.$this->moo;
        }

        if ($this->road) {
            $addressParts[] = 'ถนน' . $this->road;
        }

        if ($this->tambon) {
            $addressParts[] = 'ต.' . $this->tambon->name;
        }

        if ($this->amphur) {
            $addressParts[] = 'อ.' . $this->amphur->name;
        }

        if ($this->changwat) {
            $addressParts[] = 'จ.' . $this->changwat->name;
        }

        if ($this->zipcode) {
            $addressParts[] = $this->zipcode;
        }

        return implode(' ', $addressParts);
    }
}
