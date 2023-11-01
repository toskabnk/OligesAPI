<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'road_type',
        'road_name',
        'road_number',
        'road_letter',
        'road_km',
        'block',
        'portal',
        'stair',
        'floor',
        'door',
        'town_entity',
        'town',
        'province',
        'country',
        'postal_code'
    ];

    protected $hidden = [

    ];

    public function cooperative(): HasOne{
        return $this->hasOne(Coooperative::class);
    }

    public function farmer(): HasOne{
        return $this->hasOne(Farmer::class);
    }

    public function farm(): HasOne {
        return $this->hasOne(Farm::class);
    }
}
