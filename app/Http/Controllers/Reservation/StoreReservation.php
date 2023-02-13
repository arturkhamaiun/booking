<?php

namespace App\Http\Controllers\Reservation;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Vacancy;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreReservation extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\StoreReservationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(StoreReservationRequest $request): JsonResponse
    {
        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate = Carbon::parse($request->end_date)->toDateString();
        $requiredVacanciesCount = CarbonPeriod::create($request->start_date, $request->end_date)->count();
        $vacanciesQuery = Vacancy::whereBetween('date', [$startDate, $endDate])->where("total", ">", 0);
        $someVacanciesAreNotAvailableException = ValidationException::withMessages([
            'start_date' => ['Some vacancies between provided start and end date are not available.'],
        ]);

        $reservation = DB::transaction(function () use (
            $request,
            $requiredVacanciesCount,
            $vacanciesQuery,
            $someVacanciesAreNotAvailableException,
            $startDate,
            $endDate,
        ) {
            /** @var \Illuminate\Support\Collection $vacancies */
            $vacancies = $vacanciesQuery->lockForUpdate()->get();

            throw_if($vacancies->count() !== $requiredVacanciesCount, $someVacanciesAreNotAvailableException);

            $vacanciesQuery->decrement('total');

            return Reservation::create([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => $request->user()->id,
                'status' => ReservationStatus::NEW,
                'price' => $vacancies->reduce(fn (int $carry, Vacancy $vacancy) => $carry + $vacancy->price, 0)
            ]);
        });

        return ReservationResource::make($reservation)->response()->setStatusCode(201);
    }
}
