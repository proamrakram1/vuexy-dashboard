<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licensed extends Model
{
    use HasFactory;

    protected $fillalbe = [
        'name'
    ];

    public function realEstate()
    {
        return $this->belongsTo(RealEstate::class, 'land_type_id', 'id');
    }

    public function scopeData($query)
    {
        return $query->select([
            'id',
            'name'
        ]);
    }
}
