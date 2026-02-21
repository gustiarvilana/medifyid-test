<?php

namespace App\Http\Controllers;

use App\Models\MasterItem;
use App\Models\Kategori;
use App\Exports\MasterItemsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;
        $hargamin = $request->hargamin;
        $hargamax = $request->hargamax;
        $show_deleted = $request->show_deleted;

        $data_search = MasterItem::query();

        if ($show_deleted == 'true') {
            $data_search = $data_search->withTrashed();
        }

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');
        if (!empty($hargamin)) $data_search = $data_search->where('harga_beli', '>=', $hargamin);
        if (!empty($hargamax)) $data_search = $data_search->where('harga_beli', '<=', $hargamax);

        $data_search = $data_search->with('kategoris')
            ->select('id', 'kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'photo', 'deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($data_search as $item) {
            $item->nama_kategori = $item->kategoris->pluck('nama')->implode(', ');
            $item->is_deleted = $item->trashed();
        }

        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function restore($id)
    {
        $item = MasterItem::withTrashed()->findOrFail($id);
        $item->restore();
        return redirect('master-items')->with('success', 'Data berhasil dikembalikan');
    }

    public function downloadExcel()
    {
        return Excel::download(new MasterItemsExport, 'master-items.xlsx');
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = new MasterItem;
        } else {
            $item = MasterItem::with('kategoris')->find($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        $data['kategoris'] = Kategori::all();
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::with('kategoris')->where('kode', $kode)->first();
        return view('master_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli' => 'required|numeric',
            'laba' => 'required|numeric',
            'supplier' => 'required|string',
            'jenis' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($method == 'new') {
            $data_item = new MasterItem;
            $last_id = MasterItem::withTrashed()->max('id') ?? 0;
            $kode = $last_id + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
        } else {
            $data_item = MasterItem::find($id);
            $kode = $data_item->kode;
        }

        if ($request->hasFile('photo')) {
            // Hapus photo lama jika ada (untuk update)
            if ($method != 'new' && $data_item->photo) {
                Storage::delete('public/photos/' . $data_item->photo);
            }

            // Upload photo baru
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/photos', $fileName);

            $data_item->photo = $fileName;
        }

        $data_item->nama = $request->nama;
        $data_item->harga_beli = $request->harga_beli;
        $data_item->laba = $request->laba;
        $data_item->kode = $kode;
        $data_item->supplier = $request->supplier;
        $data_item->jenis = $request->jenis;
        $data_item->save();

        // Sync Kategoris
        if ($request->has('kategoris')) {
            $data_item->kategoris()->sync($request->kategoris);
        } else {
            $data_item->kategoris()->detach();
        }

        return redirect('master-items');
    }

    public function delete($id)
    {
        $item = MasterItem::find($id);
        $item->delete();
        return redirect('master-items')->with('success', 'Data berhasil dihapus (Soft Delete)');
    }

    public function forceDelete($id)
    {
        $item = MasterItem::withTrashed()->findOrFail($id);

        // Hapus photo permanen jika ada (Hanya saat force delete)
        if ($item->photo) {
            Storage::delete('public/photos/' . $item->photo);
        }

        $item->forceDelete();
        return redirect('master-items')->with('success', 'Data berhasil dihapus permanen');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach ($data as $item) {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100, 1000000);
            $item->laba = rand(10, 99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi', 'Bukulapuk', 'TokoBagas', 'E Commurz', 'Blublu'];
        $random = rand(0, 4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat', 'Alkes', 'Matkes', 'Umum', 'ATK'];
        $random = rand(0, 4);
        return $array[$random];
    }
}
