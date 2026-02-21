<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KategoriController extends Controller
{
    public function index()
    {
        return view('kategoris.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;

        $data_search = Kategori::query();

        if (!empty($kode)) $data_search = $data_search->where('kode', 'LIKE', '%' . $kode . '%');
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        $data_search = $data_search->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        $item = ($method == 'new') ? new Kategori : Kategori::findOrFail($id);
        
        return view('kategoris.form.index', [
            'item' => $item,
            'method' => $method
        ]);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:kategoris,kode,' . ($id ?: 'NULL') . ',id',
        ]);

        $kategori = ($method == 'new') ? new Kategori : Kategori::findOrFail($id);
        $kategori->nama = $request->nama;
        $kategori->kode = $request->kode;
        $kategori->save();

        return redirect('/kategoris')->with('success', 'Data Kategori berhasil disimpan');
    }

    public function singleView($id)
    {
        $data = Kategori::with('items')->findOrFail($id);
        return view('kategoris.single.index', compact('data'));
    }

    public function delete($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        
        return redirect('/kategoris')->with('success', 'Data Kategori berhasil dihapus');
    }

    public function downloadPdf($id)
    {
        $data = Kategori::with('items')->findOrFail($id);
        $time = date('d/m/Y H:i:s');
        
        $pdf = Pdf::loadView('kategoris.single.pdf', compact('data', 'time'));
        
        return $pdf->download('Kategori-' . $data->kode . '.pdf');
    }
}
