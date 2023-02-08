<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShowReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');
    }

    public function test_show_reservations()
    {
        $user = User::factory()->create();
        Reservation::create([
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-02',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/api/reservations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user' => [
                        'name',
                    ],
                    'start_date',
                    'end_date',
                    'created_at',
                ]
            ],
            'links',
            'meta',
        ]);
    }

    public function test_user_only_see_his_reservations()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Reservation::create([
            'start_date' => '2023-01-01',
            'end_date' => '2023-01-02',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($otherUser)->get('/api/reservations');

        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $assertableJson) {
            $assertableJson->has('data', 0)->etc();
        });
    }
}
