<!DOCTYPE html>
<html>
<head>
    <title>Daftar Obat Retur</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .danger { color: red; font-weight: bold; }
        .warning { color: darkorange; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daftar Obat Mendekati & Kadaluarsa (Retur)</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Supplier</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>No. Batch</th>
                <th>Tanggal Expired</th>
                <th>Sisa Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $index => $batch)
            @php
                $days = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($batch->expired_date), false);
                $status = '';
                $class = '';
                if ($days < 0) {
                    $status = 'Kadaluarsa';
                    $class = 'danger';
                } elseif ($days <= 30) {
                    $status = '< 1 Bulan';
                    $class = 'danger';
                } else {
                    $status = '< 3 Bulan';
                    $class = 'warning';
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $batch->drug->suppliers->first()->name ?? '-' }}</td>
                <td>{{ $batch->drug->code }}</td>
                <td>{{ $batch->drug->name }}</td>
                <td>{{ $batch->batch_number }}</td>
                <td>{{ \Carbon\Carbon::parse($batch->expired_date)->format('d/m/Y') }}</td>
                <td>{{ $batch->qty_available }}</td>
                <td class="{{ $class }}">{{ $status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada batch yang mendekati kadaluarsa (< 90 hari).</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
