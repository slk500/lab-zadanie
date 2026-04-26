<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResultsTest extends TestCase
{
    use RefreshDatabase;

    private function createPatientWithResults(): Patient
    {
        $patient = Patient::create([
            'id'         => 1,
            'name'       => 'Piotr',
            'surname'    => 'Kowalski',
            'is_male'    => true,
            'birth_date' => '1983-04-12',
        ]);

        $order = Order::create(['id' => 1, 'patient_id' => 1]);

        TestResult::create([
            'order_id'  => $order->id,
            'name'      => 'Foo Test',
            'value'     => '1.0',
            'reference' => '0.5-2.0',
        ]);

        return $patient;
    }

    private function authHeader(Patient $patient): array
    {
        $token = JWTAuth::fromUser($patient);
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_results_returns_correct_patient_data(): void
    {
        $patient = $this->createPatientWithResults();

        $response = $this->getJson('/api/results', $this->authHeader($patient));

        $response->assertStatus(200)
            ->assertJson([
                'patient' => [
                    'id'        => 1,
                    'name'      => 'Piotr',
                    'surname'   => 'Kowalski',
                    'sex'       => 'm',
                    'birthDate' => '1983-04-12',
                ],
            ]);
    }

    public function test_results_returns_correct_orders_and_results(): void
    {
        $patient = $this->createPatientWithResults();

        $response = $this->getJson('/api/results', $this->authHeader($patient));

        $response->assertStatus(200)
            ->assertJson([
                'orders' => [
                    [
                        'orderId' => '1',
                        'results' => [
                            [
                                'name'      => 'Foo Test',
                                'value'     => '1.0',
                                'reference' => '0.5-2.0',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_results_returns_multiple_orders(): void
    {
        $patient = $this->createPatientWithResults();

        $order2 = Order::create(['id' => 2, 'patient_id' => 1]);
        TestResult::create(['order_id' => $order2->id, 'name' => 'Bar Test', 'value' => '2.0', 'reference' => '1-3']);

        $response = $this->getJson('/api/results', $this->authHeader($patient));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'orders');
    }

    public function test_female_patient_sex_is_f(): void
    {
        $patient = Patient::create([
            'id'         => 2,
            'name'       => 'Anna',
            'surname'    => 'Nowak',
            'is_male'    => false,
            'birth_date' => '1990-05-20',
        ]);
        Order::create(['id' => 1, 'patient_id' => 2]);
        TestResult::create(['order_id' => 1, 'name' => 'Test', 'value' => '1', 'reference' => '1-2']);

        $response = $this->getJson('/api/results', $this->authHeader($patient));

        $response->assertStatus(200)
            ->assertJson(['patient' => ['sex' => 'f']]);
    }

    public function test_results_returns_401_without_token(): void
    {
        $this->getJson('/api/results')->assertStatus(401);
    }

    public function test_results_returns_401_with_invalid_token(): void
    {
        $this->getJson('/api/results', ['Authorization' => 'Bearer invalid.token.here'])
            ->assertStatus(401);
    }

    public function test_results_returns_404_when_no_orders(): void
    {
        $patient = Patient::create([
            'id'         => 2,
            'name'       => 'Anna',
            'surname'    => 'Nowak',
            'is_male'    => false,
            'birth_date' => '1990-05-20',
        ]);

        $this->getJson('/api/results', $this->authHeader($patient))->assertStatus(404);
    }
}
