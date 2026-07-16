<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Drug;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    protected $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'createdBy', 'approvedBy'])
            ->latest()
            ->paginate(10);
            
        return Inertia::render('PurchaseOrder/Index', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseOrder/Create', [
            'suppliers' => Supplier::all(),
            'drugs' => Drug::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $this->poService->createPurchaseOrder($validated);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.drug', 'createdBy', 'approvedBy', 'goodsReceipts']);
        
        return Inertia::render('PurchaseOrder/Show', [
            'purchaseOrder' => $purchaseOrder
        ]);
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->poService->approvePurchaseOrder($purchaseOrder);
        
        return redirect()->back()->with('success', 'Purchase Order approved successfully.');
    }
}
