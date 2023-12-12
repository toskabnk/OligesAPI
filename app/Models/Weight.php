<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weight extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'kilos',
        'sampling',
        'container',
        'purple_percentage',
        'rehu_percetage',
        'leaves_percentage',
        'receipt_id'
    ];

    protected $hidden = [

    ];

    public function receipt(): BelongsTo{
        return $this->belongsTo(Receipt::class);
    }
}
