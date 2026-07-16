<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ForecastService;

class GenerateForecastCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farmasi:forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and evaluate demand forecast for pharmacy items';

    /**
     * Execute the console command.
     */
    public function handle(ForecastService $service)
    {
        $this->info('Starting forecast generation and evaluation...');
        
        $service->evaluateForecasts();
        $this->info('Past forecasts evaluated.');

        $service->generateForecast('sma', 3);
        $this->info('Next month forecasts generated (SMA, 3 months history).');

        $this->info('Forecast generation complete.');
    }
}
