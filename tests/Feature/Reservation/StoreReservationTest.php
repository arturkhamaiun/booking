<?php

namespace Tests\Feature\Reservation;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Carbon;
use Tests\FeatureTestCase;

class StoreReservationTest extends FeatureTestCase
{
    public function test_create_reservation_for_one_day()
    {
        $user = User::factory()->create();
        Vacancy::create([
            'date' => now(),
            'total' => 1,
            'price' => 100,
        ]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'start_date' => now(),
            'end_date' => now(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
                'status',
                'price',
            ],
        ]);
        $this->assertDatabaseHas((new Reservation())->getTable(), [
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'user_id' => $user->id,
            'price' => 100,
        ]);
        $this->assertDatabaseHas((new Vacancy())->getTable(), [
            'date' => now()->toDateString(),
            'total' => 0,
        ]);
    }

    public function test_create_reservation_for_any_amount_of_days()
    {
        $user = User::factory()->create();
        $randomInt = rand(1, 10);
        $randomPeriod = now()->toPeriod(now()->addDays($randomInt));

        $randomPeriod->forEach(function (Carbon $date) {
            Vacancy::create([
                'date' => $date,
                'total' => 1,
                'price' => 10
            ]);
        });

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'start_date' => $randomPeriod->getStartDate(),
            'end_date' => $randomPeriod->getEndDate(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
                'status',
                'price',
            ],
        ]);
        $this->assertDatabaseHas((new Reservation())->getTable(), [
            'start_date' => $randomPeriod->getStartDate()->toDateString(),
            'end_date' => $randomPeriod->getEndDate()->toDateString(),
            'user_id' => $user->id,
            'price' => ($randomInt + 1) * 10
        ]);

        $randomPeriod->forEach(function ($date) {
            $this->assertDatabaseHas((new Vacancy())->getTable(), [
                'date' => $date->toDateString(),
                'total' => 0,
            ]);
        });
    }

    public function test_reservation_start_dane_is_required()
    {
        $response = $this->actingAs(User::factory()->create())->post(route('reservations.store'), [
            'start_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_end_dane_is_required()
    {
        $response = $this->actingAs(User::factory()->create())->post(route('reservations.store'), [
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_start_date_must_be_greater_or_equals_end_date()
    {
        $response = $this->actingAs(User::factory()->create())->post(route('reservations.store'), [
            'start_date' => now()->addDay(),
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_fails_if_there_no_vacancies_available()
    {
        $response = $this->actingAs(User::factory()->create())->post(route('reservations.store'), [
            'start_date' => now(),
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
        $response->assertSee('Some vacancies between provided start and end date are not available.');
    }
}
