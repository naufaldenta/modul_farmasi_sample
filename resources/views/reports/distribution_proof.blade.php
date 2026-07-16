<!DOCTYPE html>
<html>
<head>
    <title>Bukti Distribusi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-table { width: 50%; margin-bottom: 20px; border: none; }
        .info-table td { border: none; padding: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bukti Distribusi Obat</h2>
        <p>No. Distribusi: #{{ str_pad($distribution->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Tanggal:</strong></td>
            <td>{{ \Carbon\Carbon::parse($distribution->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Unit Tujuan:</strong></td>
            <td>{{ $distribution->unit_name }}</td>
        </tr>
        <tr>
            <td><strong>Pemohon:</strong></td>
            <td>{{ $distribution->requestedBy->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Disetujui Oleh:</strong></td>
            <td>{{ $distribution->approvedBy->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Catatan:</strong></td>
            <td>{{ $distribution->notes ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Qty Diminta</th>
                <th>Realisasi (Batch)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distribution->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->drug->code }}</td>
                <td>{{ $item->drug->name }}</td>
                <td>{{ $item->qty_requested }}</td>
                <td>
                    @php
                        // Cari transaksi keluar terkait distribusi ini untuk obat ini
                        $trxs = $distribution->stockTransactions->where('drug_id', $item->drug_id);
                    @endphp
                    @if($trxs->isEmpty())
                        Tidak ada alokasi
                    @else
                        <ul>
                        @foreach($trxs as $trx)
                            <li>{{ $trx->drugBatch->batch_number }} ({{ $trx->qty }} {{ $item->drug->unit }}) - ED: {{ \Carbon\Carbon::parse($trx->drugBatch->expired_date)->format('d/m/Y') }}</li>
                        @endforeach
                        </ul>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
