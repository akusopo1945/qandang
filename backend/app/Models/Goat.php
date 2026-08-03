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
        'purpose',
        'reproduction_status',
        'estimated_delivery_date',
        'birth_date',
        'initial_weight',
        'current_weight',
        'target_weight',
        'height',
        'description',
        'dam_id',
        'sire_id',
        'image',
        'price',
        'sale_status',
        'is_featured',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->qr_code)) {
                $model->qr_code = self::generateUniqueCode($model);
            }
        });
    }

    /**
     * Generate a complex and unique goat code.
     * Format: QDG-[YEAR][GENDER]-[BREED_SHORT]-[RANDOM]
     */
    public static function generateUniqueCode($model)
    {
        $year = $model->birth_date ? date('y', strtotime($model->birth_date)) : date('y');
        $gender = strtoupper(substr($model->gender ?? 'U', 0, 1)); // M/F/U
        $breed = strtoupper(substr($model->breed ?? 'LOK', 0, 3));
        $random = strtoupper(bin2hex(random_bytes(2))); // 4 random chars

        $code = "QDG-{$year}{$gender}-{$breed}-{$random}";

        // Ensure uniqueness
        while (self::where('qr_code', $code)->exists()) {
            $random = strtoupper(bin2hex(random_bytes(2)));
            $code = "QDG-{$year}{$gender}-{$breed}-{$random}";
        }

        return $code;
    }

    /**
     * Use qr_code instead of id for routing.
     */
    public function getRouteKeyName()
    {
        return 'qr_code';
    }

    public function bids(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function wishlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

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
