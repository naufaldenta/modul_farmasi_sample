<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResearchEvaluationService;
use Symfony\Component\Console\Helper\Table;

class RunResearchEvaluationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farmasi:research-evaluate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan serangkaian evaluasi akademik (FEFO Compliance, Waste Simulation, Forecast Accuracy) untuk keperluan riset.';

    /**
     * Execute the console command.
     */
    public function handle(ResearchEvaluationService $evaluator)
    {
        $this->info('Memulai Evaluasi Riset Modul Farmasi...');
        $this->newLine();

        // 1. FEFO Compliance
        $this->info('--- 1. FEFO Compliance ---');
        $compliance = $evaluator->calculateFefoCompliance();
        $this->line("Total Transaksi Keluar    : " . number_format($compliance['total_transactions']));
        $this->line("Transaksi FEFO Compliant  : " . number_format($compliance['compliant_transactions']));
        $this->info("Kepatuhan FEFO (Rate)     : " . $compliance['compliance_rate'] . "%");
        $this->newLine();

        // 2. Waste Simulation
        $this->info('--- 2. Simulasi Waste (FEFO vs FIFO) ---');
        $this->warn('Menghitung waste berdasarkan sisa batch kadaluarsa...');
        $waste = $evaluator->simulateWaste();
        
        $table = new Table($this->output);
        $table->setHeaders(['Strategi', 'Total Waste (Qty)', 'Total Kerugian (Rp)']);
        $table->setRows([
            ['FIFO (Simulasi)', number_format($waste['fifo']['waste_qty']), 'Rp ' . number_format($waste['fifo']['waste_value'], 2)],
            ['FEFO (Aktual)', number_format($waste['fefo']['waste_qty']), 'Rp ' . number_format($waste['fefo']['waste_value'], 2)],
            ['Penghematan', number_format($waste['savings_qty']), 'Rp ' . number_format($waste['savings_value'], 2)],
        ]);
        $table->render();
        $this->newLine();

        // 3. Forecast Accuracy (Train/Test Split)
        $this->info('--- 3. Evaluasi Akurasi Forecast (SMA-3, Split: 9 Train / 3 Test) ---');
        $this->warn('Melakukan replikasi kalkulasi MAE & MAPE untuk data tes...');
        $forecast = $evaluator->evaluateForecastSplit(9, 3, 3);
        
        $this->info("Obat Dievaluasi : " . $forecast['evaluated_drugs']);
        $this->info("Overall MAE     : " . $forecast['overall_mae']);
        $this->info("Overall MAPE    : " . $forecast['overall_mape'] . "%");
        
        // Print top 10 details just to keep console clean
        $details = collect($forecast['details'])->take(10)->toArray();
        if (count($details) > 0) {
            $table2 = new Table($this->output);
            $table2->setHeaders(['Nama Obat', 'Average MAE', 'Average MAPE (%)']);
            $table2->setRows($details);
            $table2->render();
            $this->line('*(Menampilkan 10 obat pertama...)*');
        }

        $this->newLine();
        $this->info('Evaluasi Selesai.');
    }
}
