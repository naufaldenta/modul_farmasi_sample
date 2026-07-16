<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\DistributionItem;
use Illuminate\Support\Facades\DB;
use Exception;

class DistributionService
{
    protected $fefoEngine;

    public function __construct(FefoEngineService $fefoEngine)
    {
        $this->fefoEngine = $fefoEngine;
    }

    /**
     * Create a new distribution draft.
     * 
     * @param array $data
     * @return Distribution
     */
    public function createDraft(array $data): Distribution
    {
        return DB::transaction(function () use ($data) {
            $distribution = Distribution::create([
                'distribution_number' => $data['distribution_number'] ?? $this->generateDistributionNumber(),
                'unit_name' => $data['unit_name'],
                'notes' => $data['notes'] ?? null,
                'requested_by' => auth()->id(),
                'status' => 'pending'
            ]);

            foreach ($data['items'] as $itemData) {
                DistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'drug_id' => $itemData['drug_id'],
                    'qty_requested' => $itemData['qty_requested'],
                ]);
            }

            return $distribution;
        });
    }

    /**
     * Approve a distribution request and deduct stock using FEFO.
     * 
     * @param Distribution $distribution
     * @param array $approvedItems Data of approved quantities (e.g. ['item_id' => qty])
     * @return Distribution
     * @throws Exception
     */
    public function approve(Distribution $distribution, array $approvedItems = []): Distribution
    {
        if ($distribution->status !== 'pending') {
            throw new Exception("Only pending distributions can be approved.");
        }

        return DB::transaction(function () use ($distribution, $approvedItems) {
            foreach ($distribution->items as $item) {
                $qtyApproved = $approvedItems[$item->id] ?? $item->qty_requested;
                $item->update(['qty_approved' => $qtyApproved]);

                if ($qtyApproved > 0) {
                    $this->fefoEngine->issue($item->drug_id, $qtyApproved, $distribution);
                }
            }

            $distribution->update([
                'status' => 'approved',
                'approved_by' => auth()->id()
            ]);

            return $distribution;
        });
    }

    /**
     * Reject a distribution request.
     * 
     * @param Distribution $distribution
     * @return Distribution
     */
    public function reject(Distribution $distribution): Distribution
    {
        if ($distribution->status !== 'pending') {
            throw new Exception("Only pending distributions can be rejected.");
        }

        $distribution->update([
            'status' => 'rejected',
            'approved_by' => auth()->id()
        ]);

        return $distribution;
    }

    /**
     * Helper to generate unique distribution number.
     * 
     * @return string
     */
    private function generateDistributionNumber(): string
    {
        $prefix = 'DST-' . date('Ymd') . '-';
        $lastDistribution = Distribution::where('distribution_number', 'like', $prefix . '%')
            ->orderBy('distribution_number', 'desc')
            ->first();

        if (!$lastDistribution) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastDistribution->distribution_number, -4);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
