<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\User;
use App\Services\PurchaseOrderService;

class PurchaseOrderServiceTest extends TestCase
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
    }

    public function test_it_can_create_purchase_order()
    {
        $service = new PurchaseOrderService();
        $po = $service->createPurchaseOrder([
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

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'drug_id' => $this->drug->id,
            'qty' => 100,
            'price' => 4500,
        ]);
    }

    public function test_it_can_approve_purchase_order()
    {
        $service = new PurchaseOrderService();
        $po = $service->createPurchaseOrder([
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

        $service->approvePurchaseOrder($po);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
    }
}
