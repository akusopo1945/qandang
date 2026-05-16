<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = [
        'goat_id',
        'type',
        'title',
        'description',
        'date_recorded',
        'status',
        'next_scheduled_date',
        'image',
    ];

    public function goat(): BelongsTo
    {
        return $this->belongsTo(Goat::class);
    }
}
