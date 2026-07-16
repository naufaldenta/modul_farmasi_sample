<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penerimaan Obat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Rekapitulasi Penerimaan Obat</h2>
        <p>Periode: {{ $startDate }} s.d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>No. Batch</th>
                <th>ED</th>
                <th>Qty Diterima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $trx->drug->suppliers->first()->name ?? '-' }}</td>
                <td>{{ $trx->drug->code }}</td>
                <td>{{ $trx->drug->name }}</td>
                <td>{{ $trx->drugBatch->batch_number }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->drugBatch->expired_date)->format('d/m/Y') }}</td>
                <td>{{ $trx->qty }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data penerimaan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
