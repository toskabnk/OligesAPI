<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo{
        return $this->belongsTo(Address::class);
    }

    public function receipts(): HasMany{
        return $this->hasMany(Receipt::class);
    }

    public function farmers(): BelongsToMany{
        return $this->belongsToMany(Farmer::class)->withPivot('partner','active')->withTimestamps();
    }
}
