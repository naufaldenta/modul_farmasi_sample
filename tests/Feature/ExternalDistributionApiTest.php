<?php

namespace Tests\Feature;

use App\Models\Drug;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExternalDistributionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed standard roles since we need them for users
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_api_rejects_unauthenticated_requests()
    {
        $response = $this->postJson('/api/distributions', [
            'unit_name' => 'Poli Umum',
            'items' => [
                ['drug_id' => 1, 'qty_requested' => 10]
            ]
        ]);

        $response->assertStatus(401);
    }

    public function test_api_accepts_valid_distribution_request_and_creates_draft()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $drug = Drug::create([
            'code' => 'D001',
            'name' => 'Paracetamol',
            'unit' => 'Tablet',
        ]);

        $payload = [
            'unit_name' => 'RME Rawat Inap',
            'notes' => 'Cito',
            'items' => [
                ['drug_id' => $drug->id, 'qty_requested' => 5]
            ]
        ];

        $response = $this->postJson('/api/distributions', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Distribusi berhasil diajukan.',
                 ]);

        // Verify it was created in the DB as pending
        $this->assertDatabaseHas('distributions', [
            'unit_name' => 'RME Rawat Inap',
            'status' => 'pending',
            'notes' => 'Cito',
        ]);

        $this->assertDatabaseHas('distribution_items', [
            'drug_id' => $drug->id,
            'qty_requested' => 5,
        ]);
    }
}
