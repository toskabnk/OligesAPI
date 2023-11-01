<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cooperative extends Model
{
    use HasFactory;

    protected $fillable = [
        'nif',
        'name',
        'phone_number',
        'user_id',
        'address_id'
    ];

    protected $hidden = [

    ];

    //Relacion 1 a 1 User
    public function user(): HasOne{
        return $this->hasOne(User::class);
    }

    public function address(): HasOne{
        return $this->hasOne(Address::class);
    }

    public function receipts(): HasMany{
        return $this->hasMany(Receipt::class);
    }

    //TODO: Hacer tabla intermedia para relacionar Farmers y Cooperativas

}
