<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createPatient(): Patient
    {
        return Patient::create([
            'id'         => 1,
            'name'       => 'Piotr',
            'surname'    => 'Kowalski',
            'is_male'    => true,
            'birth_date' => '1983-04-12',
        ]);
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $this->createPatient();

        $response = $this->postJson('/api/login', [
            'login'    => 'PiotrKowalski',
            'password' => '1983-04-12',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    public function test_login_returns_401_with_wrong_password(): void
    {
        $this->createPatient();

        $response = $this->postJson('/api/login', [
            'login'    => 'PiotrKowalski',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_returns_401_with_unknown_user(): void
    {
        $this->postJson('/api/login', [
            'login'    => 'JanNowak',
            'password' => '2000-01-01',
        ])->assertStatus(401);
    }

public function test_login_requires_login_and_password(): void
    {
        $this->postJson('/api/login', [])->assertStatus(422);
    }

    public function test_login_requires_login_field(): void
    {
        $this->postJson('/api/login', ['password' => '1983-04-12'])->assertStatus(422);
    }

    public function test_login_requires_password_field(): void
    {
        $this->postJson('/api/login', ['login' => 'PiotrKowalski'])->assertStatus(422);
    }
}
