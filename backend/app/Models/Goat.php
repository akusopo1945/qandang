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
    ];

    public function weightLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WeightLog::class);
    }

    public function healthRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }
}
