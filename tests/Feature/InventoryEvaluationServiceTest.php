<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\DrugBatch;
use App\Models\PurchaseOrder;
use App\Models\ReorderAlert;
use App\Services\InventoryEvaluationService;
use Illuminate\Support\Carbon;

class InventoryEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryEvaluationService $service;
    protected $systemUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryEvaluationService();
        $this->systemUser = \App\Models\User::firstOrCreate(
            ['email' => 'system@farmasi.local'],
            ['name' => 'System', 'password' => bcrypt('password'), 'id' => 1]
        );
    }

    protected function createDrug($overrides = [])
    {
        $category = DrugCategory::firstOrCreate(['name' => 'General']);
        return Drug::create(array_merge([
            'category_id' => $category->id,
            'code' => 'DRG-' . rand(1000, 9999),
            'name' => 'Test Drug',
            'unit' => 'Tablet',
            'price' => 1000,
            'min_stock' => 10,
            'avg_daily_usage' => 0,
            'safety_stock' => 0,
            'rop' => 0,
        ], $overrides));
    }

    public function test_it_calculates_avg_daily_usage_correctly()
    {
        $drug = $this->createDrug();
        
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 50,
            'transaction_date' => Carbon::today()->subDays(5)
        ]);
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 30,
            'transaction_date' => Carbon::today()->subDays(2)
        ]);
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 20,
            'transaction_date' => Carbon::today()->subDays(1)
        ]);
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'masuk',
            'qty' => 100,
            'transaction_date' => Carbon::today()->subDays(3)
        ]);

        $avg = $this->service->calculateAvgDailyUsage($drug->id, 10);
        $this->assertEquals(10, $avg);
    }

    public function test_it_prioritizes_fastest_supplier_and_triggers_auto_po()
    {
        $drug = $this->createDrug(['price' => 1000]);
        
        $slowSupplier = Supplier::create(['name' => 'Slow', 'contact' => '123', 'lead_time_days' => 5]);
        $fastSupplier = Supplier::create(['name' => 'Fast', 'contact' => '123', 'lead_time_days' => 2]);
        
        $drug->suppliers()->attach([$slowSupplier->id, $fastSupplier->id]);

        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 200,
            'transaction_date' => Carbon::today()->subDays(1)
        ]);

        $this->service->evaluateInventory(10, 3);
        
        $drug->refresh();
        $this->assertEquals(20, $drug->avg_daily_usage);
        $this->assertEquals(60, $drug->safety_stock);
        $this->assertEquals(100, $drug->rop);

        $po = PurchaseOrder::where('is_auto', true)->first();
        $this->assertNotNull($po);
        $this->assertEquals($fastSupplier->id, $po->supplier_id);
        
        // Assert order qty is max(ROP * 2, 10) -> 200
        $this->assertEquals(200, $po->items()->first()->qty);
        
        $this->assertDatabaseHas('reorder_alerts', [
            'drug_id' => $drug->id,
            'status' => 'unread'
        ]);
    }

    public function test_it_does_not_trigger_auto_po_if_stock_above_rop()
    {
        $drug = $this->createDrug();
        
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 10,
            'transaction_date' => Carbon::today()->subDays(1)
        ]);

        DrugBatch::create([
            'drug_id' => $drug->id,
            'batch_number' => 'BATCH-1',
            'expired_date' => Carbon::today()->addMonths(1),
            'qty_received' => 10,
            'qty_available' => 10
        ]);

        $this->service->evaluateInventory(10, 3);

        $drug->refresh();
        $this->assertEquals(4, $drug->rop);

        $this->assertDatabaseEmpty('purchase_orders');
        $this->assertDatabaseEmpty('reorder_alerts');
    }

    public function test_it_does_not_duplicate_auto_po_if_already_pending()
    {
        $drug = $this->createDrug();
        $supplier = Supplier::create(['name' => 'Fast', 'contact' => '123', 'lead_time_days' => 1]);
        $drug->suppliers()->attach($supplier->id);

        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 100,
            'transaction_date' => Carbon::today()->subDays(1)
        ]);

        $this->service->evaluateInventory(10, 3);
        $this->assertDatabaseCount('purchase_orders', 1);

        $this->service->evaluateInventory(10, 3);
        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_it_classifies_abc_correctly()
    {
        $drug1 = $this->createDrug(['avg_daily_usage' => 10, 'price' => 7500]);
        $drug2 = $this->createDrug(['avg_daily_usage' => 10, 'price' => 1500]);
        $drug3 = $this->createDrug(['avg_daily_usage' => 10, 'price' => 1000]);

        $this->service->classifyABC(1);

        $this->assertEquals('A', $drug1->fresh()->abc_class);
        $this->assertEquals('B', $drug2->fresh()->abc_class);
        $this->assertEquals('C', $drug3->fresh()->abc_class);
    }
}
