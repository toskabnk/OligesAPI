<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function farmer(): BelongsTo{
        return $this->belongsTo(Farmer::class);
    }

    public function address(): BelongsTo{
        return $this->belongsTo(Address::class);
    }
}
