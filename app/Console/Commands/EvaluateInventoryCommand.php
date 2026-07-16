<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryEvaluationService;

class EvaluateInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farmasi:evaluate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate inventory: calculate ROP, Safety Stock, Auto-PO, and classify ABC';

    /**
     * Execute the console command.
     */
    public function handle(InventoryEvaluationService $service)
    {
        $this->info('Starting inventory evaluation...');

        try {
            $service->evaluateInventory();
            $this->info('Inventory evaluation completed (ROP, Safety Stock, Auto-PO).');

            $service->classifyABC();
            $this->info('ABC Classification completed.');

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Inventory evaluation failed: ' . $e->getMessage());
            return static::FAILURE;
        }
    }
}
