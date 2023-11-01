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
        'farmer_id'
    ];

    protected $hidden = [

    ];

    public function cooperative(): BelongsTo{
        return $this->belongsTo(Cooperative::class);
    }

    public function farmer(): BelongsTo{
        return $this->belongsTo(Farmer::class);
    }

    public function receipts(): HasMany{
        return $this->hasMany(Weight::class);
    }
}
