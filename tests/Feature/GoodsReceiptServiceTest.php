<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\User;
use App\Models\DrugBatch;
use App\Models\StockTransaction;
use App\Services\PurchaseOrderService;
use App\Services\GoodsReceiptService;
use Exception;
use Mockery;
use Illuminate\Support\Facades\DB;

class GoodsReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->category = DrugCategory::create([
            'name' => 'Tablet',
            'description' => 'Obat Tablet'
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PT Mitra Sehat',
            'contact' => '08123456789',
            'lead_time_days' => 3
        ]);

        $this->drug = Drug::create([
            'name' => 'Paracetamol',
            'code' => 'PRC-001',
            'drug_category_id' => $this->category->id,
            'unit' => 'Strip',
            'price' => 5000,
            'min_stock' => 10,
        ]);

        $poService = new PurchaseOrderService();
        $this->po = $poService->createPurchaseOrder([
            'supplier_id' => $this->supplier->id,
            'po_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'drug_id' => $this->drug->id,
                    'qty' => 100,
                    'price' => 4500,
                ]
            ]
        ]);
        $poService->approvePurchaseOrder($this->po);
    }

    public function test_it_can_receive_goods_and_create_batch_and_ledger()
    {
        $grService = new GoodsReceiptService();
        $poItem = $this->po->items()->first();

        $grService->createGoodsReceipt([
            'purchase_order_id' => $this->po->id,
            'receipt_date' => now()->format('Y-m-d'),
            'notes' => 'Received OK',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'drug_id' => $this->drug->id,
                    'batch_number' => 'BATCH-001',
                    'expired_date' => now()->addMonths(12)->format('Y-m-d'),
                    'qty' => 100,
                ]
            ]
        ]);

        // Assert GR Created
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $this->po->id,
            'received_by' => $this->user->id,
        ]);

        // Assert Batch Created
        $this->assertDatabaseHas('drug_batches', [
            'drug_id' => $this->drug->id,
            'batch_number' => 'BATCH-001',
            'qty_received' => 100,
            'qty_available' => 100,
        ]);

        // Assert Stock Transaction Created
        $this->assertDatabaseHas('stock_transactions', [
            'drug_id' => $this->drug->id,
            'type' => 'masuk',
            'qty' => 100,
            'user_id' => $this->user->id,
        ]);

        // Assert PO Status Updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $this->po->id,
            'status' => 'received',
        ]);
    }

    public function test_it_adds_qty_to_existing_batch()
    {
        // First receipt
        $grService = new GoodsReceiptService();
        $poItem = $this->po->items()->first();

        $grService->createGoodsReceipt([
            'purchase_order_id' => $this->po->id,
            'receipt_date' => now()->format('Y-m-d'),
            'notes' => 'First Receipt',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'drug_id' => $this->drug->id,
                    'batch_number' => 'BATCH-SAME',
                    'expired_date' => now()->addMonths(12)->format('Y-m-d'),
                    'qty' => 50,
                ]
            ]
        ]);

        $batchId = DrugBatch::where('batch_number', 'BATCH-SAME')->first()->id;

        // Second receipt with same batch number
        // We'll create another PO to receive again
        $poService = new PurchaseOrderService();
        $po2 = $poService->createPurchaseOrder([
            'supplier_id' => $this->supplier->id,
            'po_date' => now()->format('Y-m-d'),
            'items' => [['drug_id' => $this->drug->id, 'qty' => 50, 'price' => 4500]]
        ]);
        $poService->approvePurchaseOrder($po2);
        
        $grService->createGoodsReceipt([
            'purchase_order_id' => $po2->id,
            'receipt_date' => now()->format('Y-m-d'),
            'notes' => 'Second Receipt',
            'items' => [
                [
                    'purchase_order_item_id' => $po2->items()->first()->id,
                    'drug_id' => $this->drug->id,
                    'batch_number' => 'BATCH-SAME',
                    'expired_date' => now()->addMonths(12)->format('Y-m-d'), // same expiry
                    'qty' => 50,
                ]
            ]
        ]);

        // Assert only one batch exists and qty is summed
        $this->assertEquals(1, DrugBatch::where('batch_number', 'BATCH-SAME')->count());
        $this->assertDatabaseHas('drug_batches', [
            'id' => $batchId,
            'batch_number' => 'BATCH-SAME',
            'qty_received' => 100,
            'qty_available' => 100,
        ]);
    }

    public function test_db_transaction_rolls_back_on_error()
    {
        $grService = new GoodsReceiptService();
        $poItem = $this->po->items()->first();

        try {
            $grService->createGoodsReceipt([
                'purchase_order_id' => $this->po->id,
                'receipt_date' => now()->format('Y-m-d'),
                'notes' => 'Error Receipt',
                'items' => [
                    [
                        'purchase_order_item_id' => $poItem->id,
                        'drug_id' => $this->drug->id,
                        'batch_number' => 'BATCH-ERROR',
                        'expired_date' => now()->addMonths(12)->format('Y-m-d'),
                        'qty' => 100,
                        // Simulate an error by missing required fields in underlying model maybe?
                        // Or we can mock a model to throw exception. 
                        // But an easier way is passing an invalid drug_id to trigger a SQL exception.
                    ],
                    [
                        'purchase_order_item_id' => $poItem->id,
                        'drug_id' => 9999, // Invalid drug_id will cause foreign key constraint violation
                        'batch_number' => 'BATCH-ERROR-2',
                        'expired_date' => now()->addMonths(12)->format('Y-m-d'),
                        'qty' => 10,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            // Expected
        }

        // Assert nothing was created
        $this->assertDatabaseMissing('goods_receipts', ['notes' => 'Error Receipt']);
        $this->assertDatabaseMissing('drug_batches', ['batch_number' => 'BATCH-ERROR']);
        $this->assertDatabaseMissing('stock_transactions', ['type' => 'masuk']);
    }
}
