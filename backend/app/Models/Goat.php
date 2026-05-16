<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goat extends Model
{
    protected $fillable = [
        'qr_code',
        'name',
        'breed',
        'gender',
        'birth_date',
        'initial_weight',
        'description',
        'dam_id',
        'sire_id',
        'image',
    ];

    public function dam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Goat::class, 'dam_id');
    }

    public function sire(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Goat::class, 'sire_id');
    }

    public function offspring(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goat::class, 'dam_id')->orWhere('sire_id', $this->id);
    }

    public function weightLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WeightLog::class);
    }

    public function healthRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }
}
