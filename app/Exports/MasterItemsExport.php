<?php

namespace App\Exports;

use App\Models\MasterItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterItemsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $rowNumber = 0;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return MasterItem::with('kategoris');
    }

    /**
     * @var MasterItem $item
     */
    public function map($item): array
    {
        $this->rowNumber++;
        $nama_kategori = $item->kategoris->pluck('nama')->implode(', ');
        dd($item);
        $harga_jual = $item->harga_beli + ($item->harga_beli * $item->laba / 100);
        $harga_jual = round($harga_jual);

        return [
            $this->rowNumber,
            $nama_kategori ?: '-',
            $item->nama,
            $item->supplier,
            $item->harga_beli,
            $item->laba . '%',
            $harga_jual,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Kategori',
            'Nama Items',
            'Nama Supplier',
            'Harga',
            'Laba',
            'Harga Jual',
        ];
    }
}
