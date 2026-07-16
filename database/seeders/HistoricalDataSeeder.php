<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\Supplier;
use App\Models\DrugBatch;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make it deterministic for research
        mt_srand(12345);

        // System user
        $admin = User::firstOrCreate(
            ['email' => 'admin@farmasi.local'],
            ['name' => 'System Admin', 'password' => bcrypt('password')]
        );

        $this->command->info('Creating dummy categories and suppliers...');
        $categories = collect();
        for ($i = 1; $i <= 5; $i++) {
            $categories->push(DrugCategory::firstOrCreate(['name' => "Kategori $i"]));
        }

        $suppliers = collect();
        for ($i = 1; $i <= 5; $i++) {
            $suppliers->push(Supplier::firstOrCreate(['name' => "PT Supplier $i", 'lead_time_days' => mt_rand(1, 14)]));
        }

        $this->command->info('Creating 50 dummy drugs...');
        $drugs = collect();
        for ($i = 1; $i <= 50; $i++) {
            $drugs->push(Drug::firstOrCreate(
                ['code' => "DRG-" . str_pad($i, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => "Obat Dummy $i",
                    'category_id' => $categories->random()->id,
                    'unit' => 'Tablet',
                    'price' => mt_rand(100, 1000) * 10,
                    'min_stock' => mt_rand(50, 200),
                ]
            ));
        }

        $this->command->info('Simulating 12 months of historical transactions...');
        
        $monthsToSimulate = 12;
        $startDate = Carbon::now()->subMonths($monthsToSimulate)->startOfMonth();
        
        DB::beginTransaction();
        try {
            // First, clear existing to ensure a clean slate if requested
            // (We assume running this on a fresh test DB, but let's just insert)

            $transactions = [];
            
            // Loop through each drug to generate trends
            foreach ($drugs as $drug) {
                $baseMonthlyDemand = mt_rand(100, 1000); // Base demand per month
                $trend = mt_rand(-1, 1); // -1: down, 0: stable, 1: up

                $currentDate = clone $startDate;
                
                // Initial restock
                $initialBatch = DrugBatch::create([
                    'drug_id' => $drug->id,
                    'batch_number' => "INIT-" . $drug->id . "-" . $currentDate->format('Ymd'),
                    'qty_received' => $baseMonthlyDemand * 3,
                    'qty_available' => $baseMonthlyDemand * 3,
                    'expired_date' => $currentDate->copy()->addMonths(mt_rand(6, 24)),
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);

                StockTransaction::create([
                    'drug_id' => $drug->id,
                    'drug_batch_id' => $initialBatch->id,
                    'type' => 'masuk',
                    'qty' => $initialBatch->qty_received,
                    'reference_type' => null,
                    'reference_id' => null,
                    'user_id' => $admin->id,
                    'transaction_date' => $currentDate,
                ]);

                // Simulate month by month
                for ($m = 0; $m < $monthsToSimulate; $m++) {
                    $monthDate = clone $startDate;
                    $monthDate->addMonths($m);

                    $monthlyDemand = (int)($baseMonthlyDemand * (1 + ($trend * 0.05 * $m)));
                    // Add noise
                    $monthlyDemand = (int)($monthlyDemand * (mt_rand(80, 120) / 100));

                    // Daily outgoing
                    $daysInMonth = $monthDate->daysInMonth;
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $qtyOut = (int)($monthlyDemand / $daysInMonth * (mt_rand(50, 150) / 100));
                        if ($qtyOut <= 0) continue;

                        $txDate = clone $monthDate;
                        $txDate->day($d)->addHours(mt_rand(8, 16));

                        // Find batch using FEFO (strictly compliant for simulation logic)
                        $batchToTake = DrugBatch::where('drug_id', $drug->id)
                            ->where('qty_available', '>', 0)
                            // We don't filter out expired here just to see if they get taken, 
                            // wait, normally we filter out expired. For realistic simulation, we should filter.
                            ->where('expired_date', '>=', clone $txDate) 
                            ->orderBy('expired_date', 'asc')
                            ->first();

                        if ($batchToTake && $batchToTake->qty_available >= $qtyOut) {
                            $batchToTake->qty_available -= $qtyOut;
                            $batchToTake->save();

                            $transactions[] = [
                                'drug_id' => $drug->id,
                                'drug_batch_id' => $batchToTake->id,
                                'type' => 'keluar',
                                'qty' => $qtyOut,
                                'reference_type' => null,
                                'reference_id' => null,
                                'user_id' => $admin->id,
                                'transaction_date' => clone $txDate,
                                'is_fefo_compliant' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // Monthly restock if stock is low
                    $currentStock = DrugBatch::where('drug_id', $drug->id)->sum('qty_available');
                    if ($currentStock < $drug->min_stock * 2) {
                        $restockDate = clone $monthDate;
                        $restockDate->day(mt_rand(1, 28));
                        
                        $restockQty = $drug->min_stock * 3;

                        $newBatch = DrugBatch::create([
                            'drug_id' => $drug->id,
                            'batch_number' => "BATCH-" . $drug->id . "-" . $restockDate->format('Ymd') . "-" . Str::random(4),
                            'qty_received' => $restockQty,
                            'qty_available' => $restockQty,
                            'expired_date' => $restockDate->copy()->addMonths(mt_rand(3, 18)), // create some short-lived batches for waste testing
                            'created_at' => $restockDate,
                            'updated_at' => $restockDate,
                        ]);

                        $transactions[] = [
                            'drug_id' => $drug->id,
                            'drug_batch_id' => $newBatch->id,
                            'type' => 'masuk',
                            'qty' => $restockQty,
                            'reference_type' => null,
                            'reference_id' => null,
                            'user_id' => $admin->id,
                            'transaction_date' => clone $restockDate,
                            'is_fefo_compliant' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Insert chunks to avoid memory issues
            foreach (array_chunk($transactions, 1000) as $chunk) {
                StockTransaction::insert($chunk);
            }

            DB::commit();
            $this->command->info('Generated ' . count($transactions) . ' transactions.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Failed to seed: " . $e->getMessage());
        }
    }
}
