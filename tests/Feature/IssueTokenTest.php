<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

class IssueTokenTest extends FeatureTestCase
{
    public function test_issue_token()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('super-secure-password')
        ]);

        $response = $this->post('/api/token', [
            'email' => 'test@example.com',
            'password' => 'super-secure-password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }


    public function test_issue_token_fails_because_of_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('super-secure-password')
        ]);

        $response = $this->post('/api/token', [
            'email' => 'test@example.com',
            'password' => 'random-password',
        ]);

        $response->assertStatus(422);
        $response->assertSee('The provided credentials are incorrect.');
    }
}
