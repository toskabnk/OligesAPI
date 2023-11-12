<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'sign',
        'albaran_number',
        'cooperative_id',
        'farmer_id',
        'farm_id',
        'campaign'
    ];

    protected $hidden = [

    ];

    public function cooperative(): BelongsTo{
        return $this->belongsTo(Cooperative::class);
    }

    public function farmer(): BelongsTo{
        return $this->belongsTo(Farmer::class);
    }

    public function weights(): HasMany{
        return $this->hasMany(Weight::class);
    }

    public function farm(): BelongsTo{
        return $this->belongsTo(Address::class);
    }
}
