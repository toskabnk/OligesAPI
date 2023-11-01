<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'name',
        'surname',
        'phone_number',
        'movil_number',
        'user_id',
        'address_id'
    ];

    protected $hidden = [

    ];

    public function user(): HasOne{
        return $this->hasOne(User::class);
    }

    public function address(): HasOne{
        return $this->hasOne(Address::class);
    }

    public function receipts(): HasMany{
        return $this->hasMany(Receipt::class);
    }

    public function cooperatives(): BelongsToMany{
        return $this->belongsToMany(Cooperative::class)->withPivot('partner', 'active');
    }
}
