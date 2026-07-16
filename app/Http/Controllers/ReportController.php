<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\StockTransaction;
use App\Models\DrugBatch;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReceiptsExport;
use App\Exports\DistributionsExport;
use App\Exports\ReturnsExport;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Report');
    }

    public function exportReceipts(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $format = $request->input('format', 'pdf');

        $transactions = StockTransaction::with(['drug', 'drugBatch'])
            ->where('type', 'masuk')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.receipts', compact('transactions', 'startDate', 'endDate'));
            return $pdf->download('laporan-penerimaan-' . $startDate . '-sd-' . $endDate . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReceiptsExport($transactions), 'laporan-penerimaan-' . $startDate . '-sd-' . $endDate . '.xlsx');
        }

        return back()->with('error', 'Format tidak valid.');
    }

    public function exportDistributions(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $format = $request->input('format', 'pdf');

        $transactions = StockTransaction::with(['drug', 'drugBatch'])
            ->where('type', 'keluar')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.distributions', compact('transactions', 'startDate', 'endDate'));
            return $pdf->download('laporan-distribusi-' . $startDate . '-sd-' . $endDate . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new DistributionsExport($transactions), 'laporan-distribusi-' . $startDate . '-sd-' . $endDate . '.xlsx');
        }

        return back()->with('error', 'Format tidak valid.');
    }

    public function exportReturns(Request $request)
    {
        $format = $request->input('format', 'pdf');

        // Retur: batch yang expired atau akan expired dalam 90 hari
        $threshold = Carbon::now()->addDays(90);

        $batches = DrugBatch::with(['drug.suppliers'])
            ->where('qty_available', '>', 0)
            ->where('expired_date', '<=', $threshold)
            ->orderBy('expired_date', 'asc')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.returns', compact('batches'));
            return $pdf->download('laporan-retur-obat-' . Carbon::now()->toDateString() . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReturnsExport($batches), 'laporan-retur-obat-' . Carbon::now()->toDateString() . '.xlsx');
        }

        return back()->with('error', 'Format tidak valid.');
    }
}
