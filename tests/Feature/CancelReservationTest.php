<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vacancy;
use Tests\FeatureTestCase;

class CancelReservationTest extends FeatureTestCase
{
    public function test_cancel_reservation()
    {
        $tomorrow = now()->addDay();
        $user = User::factory()->create();
        $vacancy = Vacancy::create([
            'date' => $tomorrow,
            'total' => 0,
        ]);
        $reservation = Reservation::create([
            'start_date' => $tomorrow,
            'end_date' => $tomorrow,
            'user_id' => $user->id,
            'status' => ReservationStatus::NEW,
        ]);

        $response = $this->actingAs($user)->put("/api/reservations/{$reservation->id}/cancel");

        $response->assertStatus(204);

        $this->assertDatabaseHas((new Reservation())->getTable(), [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas((new Vacancy())->getTable(), [
            'id' => $vacancy->id,
            'total' => 1,
        ]);
    }

    public function test_cant_cancel_reservation_that_is_already_started()
    {
        $now = now();
        $user = User::factory()->create();
        $reservation = Reservation::create([
            'start_date' => $now,
            'end_date' => $now,
            'user_id' => $user->id,
            'status' => ReservationStatus::NEW,
        ]);

        $response = $this->actingAs($user)->put("/api/reservations/{$reservation->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_cant_cancel_someone_else_reservation()
    {
        $now = now();
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $reservation = Reservation::create([
            'start_date' => $now,
            'end_date' => $now,
            'user_id' => $user->id,
            'status' => ReservationStatus::NEW,
        ]);

        $response = $this->actingAs($anotherUser)->put("/api/reservations/{$reservation->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_cant_cancel_that_is_already_cancelled()
    {
        $tomorrow = now()->addDay();
        $user = User::factory()->create();
        $reservation = Reservation::create([
            'start_date' => $tomorrow,
            'end_date' => $tomorrow,
            'user_id' => $user->id,
            'status' => ReservationStatus::CANCELLED,
        ]);

        $response = $this->actingAs($user)->put("/api/reservations/{$reservation->id}/cancel");

        $response->assertStatus(400);
    }
}
