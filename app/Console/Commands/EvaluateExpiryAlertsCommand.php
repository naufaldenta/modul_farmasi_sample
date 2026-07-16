<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:evaluate-expiry-alerts-command')]
#[Description('Command description')]
class EvaluateExpiryAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:evaluate-expiry-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengevaluasi batch obat dan membuat alert kedaluwarsa bertingkat (90, 30, 7, 0 hari).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expiry alerts evaluation...');

        // Ambil batch obat yang masih punya stok dan belum expired_date-nya sangat lampau (misal: lebih dari 1 tahun lalu)
        $batches = \App\Models\DrugBatch::where('qty_available', '>', 0)
            ->whereNotNull('expired_date')
            ->where('expired_date', '>=', now()->subYear())
            ->get();

        $alertsCreated = 0;

        foreach ($batches as $batch) {
            $daysToExpiry = now()->diffInDays(\Carbon\Carbon::parse($batch->expired_date), false);
            
            // Tentukan level alert berdasarkan sisa hari
            $alertLevel = null;
            if ($daysToExpiry <= 0) {
                $alertLevel = 0;
            } elseif ($daysToExpiry <= 7) {
                $alertLevel = 7;
            } elseif ($daysToExpiry <= 30) {
                $alertLevel = 30;
            } elseif ($daysToExpiry <= 90) {
                $alertLevel = 90;
            }

            if ($alertLevel !== null) {
                // Cek apakah alert dengan level yang sama atau lebih parah sudah ada (untuk mencegah duplikasi jika belum diselesaikan)
                // Kita hanya simpan per level.
                $existingAlert = \App\Models\ExpiryAlert::where('drug_batch_id', $batch->id)
                    ->where('alert_level', $alertLevel)
                    ->first();

                if (!$existingAlert) {
                    \App\Models\ExpiryAlert::create([
                        'drug_batch_id' => $batch->id,
                        'alert_level' => $alertLevel,
                        'status' => 'pending',
                        'notes' => "Sisa waktu kedaluwarsa: " . max(0, ceil($daysToExpiry)) . " hari.",
                    ]);
                    $alertsCreated++;
                }
            }
        }

        $this->info("Evaluation completed. {$alertsCreated} new alerts created.");
    }
}
