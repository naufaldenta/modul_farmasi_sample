<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    protected $grService;

    public function __construct(GoodsReceiptService $grService)
    {
        $this->grService = $grService;
    }

    public function index()
    {
        $goodsReceipts = GoodsReceipt::with(['purchaseOrder.supplier', 'receivedBy'])
            ->latest()
            ->paginate(10);
            
        return Inertia::render('GoodsReceipt/Index', [
            'goodsReceipts' => $goodsReceipts
        ]);
    }

    public function create(Request $request)
    {
        $poId = $request->query('po_id');
        $purchaseOrder = null;
        
        if ($poId) {
            $purchaseOrder = PurchaseOrder::with(['items.drug', 'supplier'])->findOrFail($poId);
        }

        return Inertia::render('GoodsReceipt/Create', [
            'purchaseOrder' => $purchaseOrder,
            'approvedPOs' => PurchaseOrder::where('status', 'approved')->with('supplier')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.batch_number' => 'required|string',
            'items.*.expired_date' => 'required|date',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $this->grService->createGoodsReceipt($validated);

        return redirect()->route('goods-receipts.index')->with('success', 'Goods Receipt created successfully.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load(['purchaseOrder.supplier', 'items.drugBatch.drug', 'receivedBy']);
        
        return Inertia::render('GoodsReceipt/Show', [
            'goodsReceipt' => $goodsReceipt
        ]);
    }
}
