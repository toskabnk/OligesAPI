<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'polygon',
        'plot',
        'farmer_id',
        'address_id'
    ];

    protected $hidden = [

    ];

    public function farmer(): HasOne{
        return $this->hasOne(Farmer::class);
    }

    public function address(): HasOne{
        return $this->hasOne(Address::class);
    }
}
