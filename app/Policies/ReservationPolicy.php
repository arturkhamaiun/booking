<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $post
     * @return bool
     */
    public function cancel(User $user, Reservation $reservation)
    {
        return $user->id === $reservation->user_id && $reservation->start_date > now();
    }
}
