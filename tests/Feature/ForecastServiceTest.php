<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\StockTransaction;
use App\Models\DemandForecast;
use App\Services\ForecastService;
use Illuminate\Support\Carbon;

class ForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ForecastService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ForecastService();
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

    public function test_it_calculates_sma_correctly()
    {
        $drug = $this->createDrug();

        // Current time for testing
        $now = Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();

        // M-1: 100
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 100,
            'transaction_date' => $startOfCurrentMonth->copy()->subMonths(1)->addDays(5)
        ]);

        // M-2: 150
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 150,
            'transaction_date' => $startOfCurrentMonth->copy()->subMonths(2)->addDays(5)
        ]);

        // M-3: 50
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 50,
            'transaction_date' => $startOfCurrentMonth->copy()->subMonths(3)->addDays(5)
        ]);

        // M-4: 200 (Should be ignored since historyPeriods = 3)
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 200,
            'transaction_date' => $startOfCurrentMonth->copy()->subMonths(4)->addDays(5)
        ]);

        $this->service->generateForecast('sma', 3);

        $forecast = DemandForecast::where('drug_id', $drug->id)->first();
        
        $this->assertNotNull($forecast);
        
        // SMA = (100 + 150 + 50) / 3 = 100
        $this->assertEquals(100, $forecast->predicted_qty);
        $this->assertEquals('sma', $forecast->method);
        $this->assertNull($forecast->actual_qty);
        $this->assertNull($forecast->mae);
        $this->assertNull($forecast->mape);
    }

    public function test_it_evaluates_mae_mape_correctly()
    {
        $drug = $this->createDrug();
        $lastMonthStart = Carbon::now()->subMonths(1)->startOfMonth();

        // Create a forecast for last month (which is already past)
        $forecast = DemandForecast::create([
            'drug_id' => $drug->id,
            'period_date' => $lastMonthStart->format('Y-m-d'),
            'method' => 'sma',
            'predicted_qty' => 100,
        ]);

        // Create transactions in last month
        // total actual = 80
        StockTransaction::create([
            'drug_id' => $drug->id,
            'type' => 'keluar',
            'qty' => 80,
            'transaction_date' => $lastMonthStart->copy()->addDays(5)
        ]);

        $this->service->evaluateForecasts();

        $forecast->refresh();

        $this->assertEquals(80, $forecast->actual_qty);
        // MAE = |80 - 100| = 20
        $this->assertEquals(20, $forecast->mae);
        // MAPE = (20 / 80) * 100 = 25
        $this->assertEquals(25, $forecast->mape);
    }

    public function test_it_handles_zero_actual_qty_for_mape()
    {
        $drug = $this->createDrug();
        $lastMonthStart = Carbon::now()->subMonths(1)->startOfMonth();

        // Create a forecast for last month (which is already past)
        $forecast = DemandForecast::create([
            'drug_id' => $drug->id,
            'period_date' => $lastMonthStart->format('Y-m-d'),
            'method' => 'sma',
            'predicted_qty' => 100,
        ]);

        // No actual transactions => actual_qty = 0

        $this->service->evaluateForecasts();

        $forecast->refresh();

        $this->assertEquals(0, $forecast->actual_qty);
        $this->assertEquals(100, $forecast->mae);
        $this->assertNull($forecast->mape); // Skip division by zero
    }
}
