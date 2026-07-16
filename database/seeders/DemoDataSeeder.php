<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DrugCategory;
use App\Models\Supplier;
use App\Models\Drug;
use App\Services\PurchaseOrderService;
use App\Services\GoodsReceiptService;
use App\Services\DistributionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    protected $poService;
    protected $grService;
    protected $distService;

    public function __construct(
        PurchaseOrderService $poService,
        GoodsReceiptService $grService,
        DistributionService $distService
    ) {
        $this->poService = $poService;
        $this->grService = $grService;
        $this->distService = $distService;
    }

    public function run(): void
    {
        mt_srand(12345); // Deterministic

        $this->command->info('Loading system users...');
        $admin = User::where('email', 'admin@simrs.com')->first();
        auth()->login($admin);

        $this->command->info('Creating Categories & Suppliers...');
        
        $categories = [
            'Antibiotik' => DrugCategory::firstOrCreate(['name' => 'Antibiotik']),
            'Analgesik' => DrugCategory::firstOrCreate(['name' => 'Analgesik']),
            'Vitamin & Suplemen' => DrugCategory::firstOrCreate(['name' => 'Vitamin & Suplemen']),
            'Obat Saluran Cerna' => DrugCategory::firstOrCreate(['name' => 'Obat Saluran Cerna']),
            'Obat Kardiovaskuler' => DrugCategory::firstOrCreate(['name' => 'Obat Kardiovaskuler']),
            'Alat Kesehatan' => DrugCategory::firstOrCreate(['name' => 'Alat Kesehatan']),
            'Injeksi Khusus' => DrugCategory::firstOrCreate(['name' => 'Injeksi Khusus']),
        ];

        $supplierNames = ['PT Kimia Farma', 'PT Kalbe Farma', 'PT Dexa Medica', 'PT Sanbe Farma', 'PT Enseval'];
        $suppliers = collect();
        foreach ($supplierNames as $sup) {
            $suppliers->push(Supplier::firstOrCreate([
                'name' => $sup,
                'phone' => '021' . mt_rand(1000000, 9999999),
                'address' => 'Jl. Industri No. ' . mt_rand(1, 100),
                'contact_person' => 'Bpk. ' . Str::random(5),
                'lead_time_days' => mt_rand(2, 5)
            ]));
        }

        $this->command->info('Creating Drugs (Fast, Medium, Slow moving)...');
        
        // Fast Moving (High Vol, Low-Med Price) -> Likely Category B or A depending on total volume
        $fastDrugs = [
            ['name' => 'Paracetamol 500mg Tab', 'cat' => 'Analgesik', 'unit' => 'Tablet', 'price' => 500, 'min' => 1000],
            ['name' => 'Amoxicillin 500mg Cap', 'cat' => 'Antibiotik', 'unit' => 'Kapsul', 'price' => 1200, 'min' => 800],
            ['name' => 'Ibuprofen 400mg Tab', 'cat' => 'Analgesik', 'unit' => 'Tablet', 'price' => 800, 'min' => 500],
            ['name' => 'Vitamin C 500mg Tab', 'cat' => 'Vitamin & Suplemen', 'unit' => 'Tablet', 'price' => 400, 'min' => 1000],
            ['name' => 'Antasida Doen Tab', 'cat' => 'Obat Saluran Cerna', 'unit' => 'Tablet', 'price' => 300, 'min' => 800],
            ['name' => 'Spuit 3cc', 'cat' => 'Alat Kesehatan', 'unit' => 'Pcs', 'price' => 2000, 'min' => 500],
        ];

        // High Value (Low-Med Vol, High Price) -> Likely Category A
        $highValueDrugs = [
            ['name' => 'Meropenem 1g Inj', 'cat' => 'Injeksi Khusus', 'unit' => 'Ampul', 'price' => 150000, 'min' => 50],
            ['name' => 'Ceftriaxone 1g Inj', 'cat' => 'Injeksi Khusus', 'unit' => 'Vial', 'price' => 35000, 'min' => 100],
            ['name' => 'Human Albumin 20% 50ml', 'cat' => 'Injeksi Khusus', 'unit' => 'Vial', 'price' => 1200000, 'min' => 10],
            ['name' => 'Amlodipine 5mg Tab', 'cat' => 'Obat Kardiovaskuler', 'unit' => 'Tablet', 'price' => 15000, 'min' => 200], // Moderate value
            ['name' => 'Clopidogrel 75mg Tab', 'cat' => 'Obat Kardiovaskuler', 'unit' => 'Tablet', 'price' => 18000, 'min' => 150],
        ];

        // Slow Moving (Low Vol, Low-Med Price) -> Likely Category C
        $slowDrugs = [
            ['name' => 'Salep 2-4', 'cat' => 'Alat Kesehatan', 'unit' => 'Tube', 'price' => 5000, 'min' => 20],
            ['name' => 'Kasa Steril 16x16', 'cat' => 'Alat Kesehatan', 'unit' => 'Box', 'price' => 12000, 'min' => 30],
            ['name' => 'Krim Ketoconazole 2%', 'cat' => 'Alat Kesehatan', 'unit' => 'Tube', 'price' => 8000, 'min' => 20],
            ['name' => 'Vitamin B Kompleks', 'cat' => 'Vitamin & Suplemen', 'unit' => 'Botol', 'price' => 15000, 'min' => 20],
        ];

        $allDrugs = [];
        $i = 1;
        foreach (array_merge($fastDrugs, $highValueDrugs, $slowDrugs) as $dData) {
            $drug = Drug::firstOrCreate(
                ['code' => "DRG-" . str_pad($i++, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $dData['name'],
                    'category_id' => $categories[$dData['cat']]->id,
                    'unit' => $dData['unit'],
                    'price' => $dData['price'],
                    'min_stock' => $dData['min'],
                ]
            );
            $drug->suppliers()->sync($suppliers->random(2)->pluck('id')->toArray());
            $allDrugs[] = ['model' => $drug, 'type' => in_array($dData, $fastDrugs) ? 'fast' : (in_array($dData, $highValueDrugs) ? 'high_value' : 'slow')];
        }

        $this->command->info('Simulating 12 Months of PO, GR, and Distributions...');
        
        $units = ['IGD', 'Poli Umum', 'Rawat Inap', 'ICU', 'OK (Kamar Operasi)'];
        $now = Carbon::now();

        // Simulate from 12 months ago to current month
        for ($monthsAgo = 12; $monthsAgo >= 0; $monthsAgo--) {
            $monthDate = $now->copy()->subMonths($monthsAgo)->startOfMonth();
            $daysInMonth = $monthDate->daysInMonth;

            $maxDay = $monthsAgo == 0 ? max(1, $now->day - 1) : 25;
            // Generate 3-5 POs per month
            $poCount = mt_rand(3, 5);
            for ($p = 0; $p < $poCount; $p++) {
                $poDate = $monthDate->copy()->addDays(mt_rand(1, $maxDay));
                $supplier = $suppliers->random();
                $poItems = [];
                
                // Pick some drugs
                foreach ($allDrugs as $d) {
                    $prob = $d['type'] === 'fast' ? 70 : ($d['type'] === 'high_value' ? 50 : 20);
                    if (mt_rand(1, 100) <= $prob) {
                        $qty = $d['type'] === 'fast' ? mt_rand(1000, 5000) : ($d['type'] === 'high_value' ? mt_rand(50, 200) : mt_rand(20, 100));
                        $poItems[] = [
                            'drug_id' => $d['model']->id,
                            'qty' => $qty,
                            'price' => $d['model']->price * (mt_rand(95, 105) / 100)
                        ];
                    }
                }

                if (empty($poItems)) continue;

                $po = $this->poService->createPurchaseOrder([
                    'supplier_id' => $supplier->id,
                    'po_number' => 'PO-' . $poDate->format('Ymd') . '-' . mt_rand(1000, 9999),
                    'po_date' => $poDate->format('Y-m-d'),
                    'items' => $poItems
                ]);

                $this->backdate($po, $poDate, 'purchase_order_items', 'purchase_order_id');

                // Receive PO
                $this->poService->approvePurchaseOrder($po);
                $receiptDate = $poDate->copy()->addDays(mt_rand(1, 3));
                
                $receiptItems = [];
                foreach ($po->items as $idx => $pi) {
                    // Force some batches to expire soon for the alert!
                    // If it's the current month and random hits, make it expire in 30-60 days
                    if ($monthsAgo == 0 && mt_rand(1, 100) <= 20) {
                        $expDate = Carbon::now()->addDays(mt_rand(15, 45));
                    } else {
                        $expDate = $receiptDate->copy()->addMonths(mt_rand(12, 36));
                    }

                    $receiptItems[] = [
                        'purchase_order_item_id' => $pi->id,
                        'drug_id' => $pi->drug_id,
                        'qty' => $pi->qty,
                        'batch_number' => 'B' . $receiptDate->format('Ym') . str_pad($idx, 2, '0', STR_PAD_LEFT),
                        'expired_date' => $expDate->format('Y-m-d'),
                    ];
                }

                $gr = $this->grService->createGoodsReceipt([
                    'purchase_order_id' => $po->id,
                    'receipt_number' => 'GR-' . $receiptDate->format('Ymd') . '-' . mt_rand(1000, 9999),
                    'receipt_date' => $receiptDate->format('Y-m-d'),
                    'notes' => 'Penerimaan otomatis',
                    'items' => $receiptItems
                ]);

                // Backdate GR and all related stock tx
                $this->backdate($gr, $receiptDate, 'goods_receipt_items', 'goods_receipt_id');
                DB::table('drug_batches')->whereIn('drug_id', collect($receiptItems)->pluck('drug_id'))->update([
                    'created_at' => $receiptDate, 'updated_at' => $receiptDate
                ]);
                DB::table('stock_transactions')->where('reference_type', 'App\Models\GoodsReceipt')->where('reference_id', $gr->id)->update([
                    'created_at' => $receiptDate, 'updated_at' => $receiptDate, 'transaction_date' => $receiptDate
                ]);
            }

            $maxDistDay = $monthsAgo == 0 ? max(1, $now->day - 1) : $daysInMonth - 1;
            // Generate Distributions for the month (almost daily or every few days)
            $distCount = mt_rand(15, 25);
            for ($d = 0; $d < $distCount; $d++) {
                $distDate = $monthDate->copy()->addDays(mt_rand(1, $maxDistDay));
                
                $distItems = [];
                foreach ($allDrugs as $drugData) {
                    $drug = $drugData['model'];
                    // Distribution logic: fast moving is distributed frequently
                    $prob = $drugData['type'] === 'fast' ? 60 : ($drugData['type'] === 'high_value' ? 30 : 10);
                    
                    // Seasonal fluctuation: in some months multiply prob
                    if ($monthDate->month >= 10 || $monthDate->month <= 2) {
                        // Flu season, Paracetamol and Vitamin C go UP
                        if (in_array($drug->name, ['Paracetamol 500mg Tab', 'Vitamin C 500mg Tab'])) {
                            $prob += 30;
                        }
                    }

                    if (mt_rand(1, 100) <= $prob) {
                        $available = DB::table('drug_batches')->where('drug_id', $drug->id)->sum('qty_available');
                        
                        // To trigger low stock alert, purposely consume heavily if it's the current month
                        $requestQty = 0;
                        if ($monthsAgo <= 1 && mt_rand(1,10) <= 2) {
                            $requestQty = intval($available * 0.9); // consume almost all!
                        } else {
                            $requestQty = $drugData['type'] === 'fast' ? mt_rand(50, 300) : ($drugData['type'] === 'high_value' ? mt_rand(5, 20) : mt_rand(1, 10));
                        }

                        if ($available >= $requestQty && $requestQty > 0) {
                            $distItems[] = [
                                'drug_id' => $drug->id,
                                'qty_requested' => $requestQty
                            ];
                        }
                    }
                }

                if (empty($distItems)) continue;

                $dist = $this->distService->createDraft([
                    'distribution_number' => 'DST-' . $distDate->format('Ymd') . '-' . mt_rand(1000, 9999),
                    'unit_name' => $units[array_rand($units)],
                    'notes' => 'Distribusi unit',
                    'items' => $distItems
                ]);

                $this->backdate($dist, $distDate, 'distribution_items', 'distribution_id');

                try {
                    $this->distService->approve($dist);
                    $dist->update(['status' => 'approved', 'updated_at' => $distDate]);
                    DB::table('stock_transactions')->where('reference_type', 'App\Models\Distribution')->where('reference_id', $dist->id)->update([
                        'created_at' => $distDate, 'updated_at' => $distDate, 'transaction_date' => $distDate
                    ]);
                } catch (\Exception $e) {
                    // Skip if FEFO error
                }
            }
        }

        $this->command->info('Demo Data Seeding completed! Real-world data generated successfully.');
    }

    private function backdate($model, $date, $childTable, $fk)
    {
        $model->update(['created_at' => $date, 'updated_at' => $date]);
        DB::table($childTable)->where($fk, $model->id)->update([
            'created_at' => $date, 'updated_at' => $date
        ]);
    }
}
