<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case NEW = 'new';
    case CANCELLED = 'cancelled';
}
