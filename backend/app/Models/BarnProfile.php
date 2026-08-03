<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarnProfile extends Model
{
    protected $fillable = [
        'name',
        'owner_name',
        'phone',
        'address',
        'village',
        'district',
        'city',
        'province',
        'capacity',
        'description',
    ];
}
