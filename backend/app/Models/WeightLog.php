<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightLog extends Model
{
    protected $fillable = [
        'goat_id',
        'weight',
        'date_recorded',
        'note',
    ];

    public function goat(): BelongsTo
    {
        return $this->belongsTo(Goat::class);
    }
}
