<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    protected $fillable = [
        'title',
        'category',
        'content',
        'order',
        'is_published',
    ];
}
