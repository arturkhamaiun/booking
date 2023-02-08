<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $with = ['user'];

    protected $casts = [
        'status' => ReservationStatus::class,
    ];

    protected $fillable = [
        'start_date',
        'end_date',
        'user_id',
        'status',
        'price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
