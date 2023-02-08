<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CancelReservation extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Reservation  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Reservation $reservation): Response
    {
        $this->authorize('cancel', $reservation);

        abort_if(
            $reservation->status === ReservationStatus::CANCELLED->value,
            400,
            'Reservation is already cancelled.'
        );

        $vacanciesQuery = Vacancy::whereBetween('date', [$reservation->start_date, $reservation->end_date]);

        DB::transaction(function () use ($reservation, $vacanciesQuery) {
            $vacanciesQuery->lockForUpdate()->count();

            $vacanciesQuery->increment('total');

            $reservation->update(['status' => ReservationStatus::CANCELLED]);
        });

        return response(null, 204);
    }
}
