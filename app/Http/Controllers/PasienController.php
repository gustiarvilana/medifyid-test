<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Exports\PasiensExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PasienController extends Controller
{
    /**
     * Display the Pasien index page
     */
    public function index()
    {
        return view('pasiens.index.index');
    }

    /**
     * Search and filter pasien data (AJAX)
     */
    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $nik = $request->nik;
        $jenis_kelamin = $request->jenis_kelamin;
        $show_deleted = $request->show_deleted;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $page = $request->page ?? 1;
        $data_per_fetch = $request->data_per_fetch ?? 500;

        $data_search = Pasien::query();

        if ($show_deleted == 'true') {
            $data_search = $data_search->withTrashed();
        }

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');
        if (!empty($nik)) $data_search = $data_search->where('nik', $nik);
        if (!empty($jenis_kelamin)) $data_search = $data_search->where('jenis_kelamin', $jenis_kelamin);
        if (!empty($start_date)) $data_search = $data_search->whereDate('tanggal_lahir', '>=', $start_date);
        if (!empty($end_date)) $data_search = $data_search->whereDate('tanggal_lahir', '<=', $end_date);

        $data_search = $data_search
            ->select('id', 'kode', 'nama', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 
                     'agama', 'alamat', 'no_telepon', 'email', 'pekerjaan', 'golongan_darah', 
                     'photo', 'deleted_at', 'created_at')
            ->orderBy('id', 'desc');

        $total_count = $data_search->count();

        $data_search = $data_search->skip(($page - 1) * $data_per_fetch)
            ->take($data_per_fetch)
            ->get();

        foreach ($data_search as $item) {
            $item->umur = $item->umur;
            $item->is_deleted = $item->trashed();
        }

        return response()->json([
            'status' => 200,
            'data' => $data_search,
            'total_count' => $total_count,
            'page' => (int)$page,
            'data_per_fetch' => (int)$data_per_fetch
        ]);
    }

    /**
     * Restore soft deleted pasien
     */
    public function restore($id)
    {
        $item = Pasien::withTrashed()->findOrFail($id);
        $item->restore();
        return redirect('pasiens')->with('success', 'Data pasien berhasil dikembalikan');
    }

    /**
     * Download Excel export
     */
    public function downloadExcel()
    {
        return Excel::download(new PasiensExport, 'pasiens.xlsx');
    }

    /**
     * Display form for create/edit
     */
    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = new Pasien;
        } else {
            $item = Pasien::find($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        return view('pasiens.form.index', $data);
    }

    /**
     * Display single pasien view
     */
    public function singleView($kode)
    {
        $data['data'] = Pasien::where('kode', $kode)->first();
        return view('pasiens.single.index', $data);
    }

    /**
     * Process form submission (create/update)
     */
    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pekerjaan' => 'nullable|string|max:100',
            'golongan_darah' => 'nullable|string|max:5',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($method == 'new') {
            $data_item = new Pasien;
            $last_id = Pasien::withTrashed()->max('id') ?? 0;
            $kode = $last_id + 1;
            $kode = str_pad($kode, 6, 'P', STR_PAD_LEFT);
        } else {
            $data_item = Pasien::find($id);
            $kode = $data_item->kode;
        }

        if ($request->hasFile('photo')) {
            // Hapus photo lama jika ada (untuk update)
            if ($method != 'new' && $data_item->photo) {
                Storage::delete('public/photos/pasien/' . $data_item->photo);
            }

            // Upload photo baru
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/photos/pasien', $fileName);

            $data_item->photo = $fileName;
        }

        $data_item->kode = $kode;
        $data_item->nama = $request->nama;
        $data_item->nik = $request->nik;
        $data_item->tempat_lahir = $request->tempat_lahir;
        $data_item->tanggal_lahir = $request->tanggal_lahir;
        $data_item->jenis_kelamin = $request->jenis_kelamin;
        $data_item->agama = $request->agama;
        $data_item->alamat = $request->alamat;
        $data_item->no_telepon = $request->no_telepon;
        $data_item->email = $request->email;
        $data_item->pekerjaan = $request->pekerjaan;
        $data_item->golongan_darah = $request->golongan_darah;
        $data_item->save();

        return redirect('pasiens')->with('success', 'Data pasien berhasil disimpan');
    }

    /**
     * Soft delete pasien
     */
    public function delete($id)
    {
        $item = Pasien::find($id);
        $item->delete();
        return redirect('pasiens')->with('success', 'Data pasien berhasil dihapus (Soft Delete)');
    }

    /**
     * Permanently delete pasien
     */
    public function forceDelete($id)
    {
        $item = Pasien::withTrashed()->findOrFail($id);

        // Hapus photo permanen jika ada
        if ($item->photo) {
            Storage::delete('public/photos/pasien/' . $item->photo);
        }

        $item->forceDelete();
        return redirect('pasiens')->with('success', 'Data pasien berhasil dihapus permanen');
    }

    /**
     * Download PDF for single pasien
     */
    public function downloadPdf($kode)
    {
        $data = Pasien::where('kode', $kode)->first();
        $time = date('d/m/Y H:i:s');

        $pdf = Pdf::loadView('pasiens.single.pdf', compact('data', 'time'));

        return $pdf->download('Pasien-' . $data->kode . '.pdf');
    }
}
