<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class DistributionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transaksi',
            'Kode Obat',
            'Nama Obat',
            'No. Batch',
            'Tanggal Kedaluwarsa',
            'Qty Keluar',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('Y-m-d H:i:s'),
            $transaction->drug->code,
            $transaction->drug->name,
            $transaction->drugBatch ? $transaction->drugBatch->batch_number : '-',
            $transaction->drugBatch ? $transaction->drugBatch->expired_date : '-',
            $transaction->qty,
        ];
    }
}
