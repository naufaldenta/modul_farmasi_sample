<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class ReturnsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $batches;

    public function __construct(Collection $batches)
    {
        $this->batches = $batches;
    }

    public function collection()
    {
        return $this->batches;
    }

    public function headings(): array
    {
        return [
            'Kode Obat',
            'Nama Obat',
            'No. Batch',
            'Tanggal Kedaluwarsa',
            'Qty Sisa',
            'Supplier',
        ];
    }

    public function map($batch): array
    {
        return [
            $batch->drug->code,
            $batch->drug->name,
            $batch->batch_number,
            $batch->expired_date,
            $batch->qty_available,
            $batch->drug->suppliers->isNotEmpty() ? $batch->drug->suppliers->pluck('name')->implode(', ') : '-',
        ];
    }
}
