<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');
    }

    public function test_create_reservation_for_one_day()
    {
        $user = User::factory()->create();
        Vacancy::create([
            'date' => '2050-01-01',
            'total' => 1,
        ]);

        $response = $this->actingAs($user)->post('/api/reservations', [
            'start_date' => "2050-01-01",
            'end_date' => "2050-01-01",
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user' => [
                    'name',
                ],
                'start_date',
                'end_date',
                'created_at',
            ],
        ]);
        $this->assertDatabaseHas((new Reservation())->getTable(), [
            'start_date' => '2050-01-01',
            'end_date' => '2050-01-01',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas((new Vacancy())->getTable(), [
            'date' => '2050-01-01',
            'total' => 0,
        ]);
    }

    public function test_create_reservation_for_any_amount_of_days()
    {
        $user = User::factory()->create();
        $randomPeriod = now()->toPeriod(now()->addDays(rand(1, 10)));

        $randomPeriod->forEach(function (Carbon $date) {
            Vacancy::create([
                'date' => $date,
                'total' => 1,
            ]);
        });

        $response = $this->actingAs($user)->post('/api/reservations', [
            'start_date' => $randomPeriod->getStartDate(),
            'end_date' => $randomPeriod->getEndDate(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user' => [
                    'name',
                ],
                'start_date',
                'end_date',
                'created_at',
            ],
        ]);
        $this->assertDatabaseHas((new Reservation())->getTable(), [
            'start_date' => $randomPeriod->getStartDate()->toDateString(),
            'end_date' => $randomPeriod->getEndDate()->toDateString(),
            'user_id' => $user->id,
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
        $response = $this->actingAs(User::factory()->create())->post('/api/reservations', [
            'start_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_end_dane_is_required()
    {
        $response = $this->actingAs(User::factory()->create())->post('/api/reservations', [
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_start_date_must_be_greater_or_equals_end_date()
    {
        $response = $this->actingAs(User::factory()->create())->post('/api/reservations', [
            'start_date' => now()->addDay(),
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
    }

    public function test_reservation_fails_if_there_no_vacancies_available()
    {
        $response = $this->actingAs(User::factory()->create())->post('/api/reservations', [
            'start_date' => now(),
            'end_date' => now(),
        ]);

        $response->assertStatus(422);
        $response->assertSee('Some vacancies between provided start and end date are not available.');
    }
}
