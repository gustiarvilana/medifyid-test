<?php

namespace App\Exports;

use App\Models\Pasien;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PasiensExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $rowNumber = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        return Pasien::query();
    }

    /**
     * Map each row to Excel columns
     */
    public function map($pasien): array
    {
        $this->rowNumber++;

        // Calculate age
        $umur = null;
        if ($pasien->tanggal_lahir) {
            $umur = $pasien->tanggal_lahir->diffInYears(now());
        }

        return [
            $this->rowNumber,
            $pasien->kode,
            $pasien->nama,
            $pasien->nik ?? '-',
            $pasien->tempat_lahir ?? '-',
            $pasien->tanggal_lahir ? $pasien->tanggal_lahir->format('d/m/Y') : '-',
            $umur ?? '-',
            $pasien->jenis_kelamin == 'L' ? 'Laki-laki' : ($pasien->jenis_kelamin == 'P' ? 'Perempuan' : '-'),
            $pasien->agama ?? '-',
            $pasien->golongan_darah ?? '-',
            $pasien->pekerjaan ?? '-',
            $pasien->alamat ?? '-',
            $pasien->no_telepon ?? '-',
            $pasien->email ?? '-',
        ];
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Kode Pasien',
            'Nama Lengkap',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Umur',
            'Jenis Kelamin',
            'Agama',
            'Gol. Darah',
            'Pekerjaan',
            'Alamat',
            'No. Telepon',
            'Email',
        ];
    }
}
