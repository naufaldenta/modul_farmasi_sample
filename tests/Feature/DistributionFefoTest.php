<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Distribution;
use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\DistributionService;
use App\Services\FefoEngineService;
use Illuminate\Support\Facades\DB;
use Exception;
class DistributionFefoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $drug;
    protected $distributionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->drug = Drug::create([
            'drug_category_id' => 1,
            'code' => 'DRG-001',
            'name' => 'Paracetamol',
            'unit' => 'Tablet',
            'min_stock' => 10,
        ]);

        $this->distributionService = new DistributionService(new FefoEngineService());
    }

    public function test_it_can_create_distribution_draft()
    {
        $data = [
            'unit_name' => 'Poli Umum',
            'notes' => 'Urgent',
            'items' => [
                ['drug_id' => $this->drug->id, 'qty_requested' => 50]
            ]
        ];

        $distribution = $this->distributionService->createDraft($data);

        $this->assertDatabaseHas('distributions', [
            'id' => $distribution->id,
            'unit_name' => 'Poli Umum',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('distribution_items', [
            'distribution_id' => $distribution->id,
            'drug_id' => $this->drug->id,
            'qty_requested' => 50
        ]);
    }

    public function test_fefo_engine_deducts_closest_expired_batch_first()
    {
        // Batch 1: Expired 1 month from now (Closest)
        DrugBatch::create([
            'drug_id' => $this->drug->id,
            'batch_number' => 'B001',
            'expired_date' => now()->addMonth(),
            'qty_received' => 100,
            'qty_available' => 100
        ]);

        // Batch 2: Expired 3 months from now
        DrugBatch::create([
            'drug_id' => $this->drug->id,
            'batch_number' => 'B002',
            'expired_date' => now()->addMonths(3),
            'qty_received' => 100,
            'qty_available' => 100
        ]);

        $distribution = $this->distributionService->createDraft([
            'unit_name' => 'Poli Umum',
            'items' => [
                ['drug_id' => $this->drug->id, 'qty_requested' => 150]
            ]
        ]);

        $this->distributionService->approve($distribution);

        // B001 should be completely used (100)
        $this->assertEquals(0, DrugBatch::where('batch_number', 'B001')->first()->qty_available);
        
        // B002 should have 50 left
        $this->assertEquals(50, DrugBatch::where('batch_number', 'B002')->first()->qty_available);

        // Verify Stock Transactions
        $this->assertEquals(2, StockTransaction::where('reference_id', $distribution->id)->count());
    }

    public function test_fefo_engine_throws_exception_if_insufficient_stock()
    {
        DrugBatch::create([
            'drug_id' => $this->drug->id,
            'batch_number' => 'B001',
            'expired_date' => now()->addMonth(),
            'qty_received' => 50,
            'qty_available' => 50
        ]);

        $distribution = $this->distributionService->createDraft([
            'unit_name' => 'Poli Umum',
            'items' => [
                ['drug_id' => $this->drug->id, 'qty_requested' => 100] // Asking more than available
            ]
        ]);

        $this->expectException(Exception::class);
        $this->distributionService->approve($distribution);
    }

    public function test_fefo_engine_ignores_expired_batches()
    {
        // Already Expired
        DrugBatch::create([
            'drug_id' => $this->drug->id,
            'batch_number' => 'B-EXPIRED',
            'expired_date' => now()->subDay(),
            'qty_received' => 100,
            'qty_available' => 100
        ]);

        // Valid
        DrugBatch::create([
            'drug_id' => $this->drug->id,
            'batch_number' => 'B-VALID',
            'expired_date' => now()->addMonth(),
            'qty_received' => 30,
            'qty_available' => 30
        ]);

        $distribution = $this->distributionService->createDraft([
            'unit_name' => 'Poli Umum',
            'items' => [
                ['drug_id' => $this->drug->id, 'qty_requested' => 50]
            ]
        ]);

        $this->expectException(Exception::class);
        $this->distributionService->approve($distribution);
    }
}
