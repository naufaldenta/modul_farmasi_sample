<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DistributionService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class ExternalDistributionController extends Controller
{
    protected $distributionService;

    public function __construct(DistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.qty_requested' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Override user id for external API since Sanctum user might be a special service account
            $distribution = $this->distributionService->createDraft($validated);
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Distribusi berhasil diajukan.',
                'data' => [
                    'distribution_number' => $distribution->distribution_number,
                    'distribution_id' => $distribution->id,
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan distribusi: ' . $e->getMessage()
            ], 500);
        }
    }
}
