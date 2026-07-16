<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Drug;
use App\Services\DistributionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    protected $distributionService;

    public function __construct(DistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    public function index()
    {
        $distributions = Distribution::with(['requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(10);
            
        return Inertia::render('Distribution/Index', [
            'distributions' => $distributions
        ]);
    }

    public function create()
    {
        $drugs = Drug::orderBy('name')->get();
        return Inertia::render('Distribution/Create', [
            'drugs' => $drugs
        ]);
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
            $this->distributionService->createDraft($validated);
            return redirect()->route('distributions.index')->with('success', 'Distribusi berhasil diajukan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengajukan distribusi: ' . $e->getMessage());
        }
    }

    public function show(Distribution $distribution)
    {
        $distribution->load(['items.drug', 'requestedBy', 'approvedBy', 'stockTransactions']);
        
        return Inertia::render('Distribution/Show', [
            'distribution' => $distribution
        ]);
    }

    public function approve(Request $request, Distribution $distribution)
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*' => 'integer|min:0'
        ]);

        try {
            $approvedItems = $validated['items'] ?? [];
            $this->distributionService->approve($distribution, $approvedItems);
            return redirect()->route('distributions.show', $distribution->id)->with('success', 'Distribusi berhasil disetujui.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Distribution $distribution)
    {
        try {
            $this->distributionService->reject($distribution);
            return redirect()->route('distributions.show', $distribution->id)->with('success', 'Distribusi ditolak.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportPdf(Distribution $distribution)
    {
        $distribution->load(['items.drug', 'requestedBy', 'approvedBy', 'stockTransactions.drugBatch']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.distribution_proof', compact('distribution'));
        return $pdf->download('bukti-distribusi-' . $distribution->id . '.pdf');
    }
}
