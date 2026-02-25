# Dialog Interview - Verifikasi Hasil Tes Coding
## MedifyID Test - Inventory Management System

---

## 📋 Daftar Isi
1. [Pemahaman Laravel Framework](#1-pemahaman-laravel-framework)
2. [Database & Relationship](#2-database--relationship)
3. [CRUD Functionality](#3-crud-functionality)
4. [AJAX & Frontend Integration](#4-ajax--frontend-integration)
5. [Export Functionality](#5-export-functionality)
6. [File Upload Handling](#6-file-upload-handling)
7. [Code Quality & Best Practices](#7-code-quality--best-practices)
8. [Testing & Debugging](#8-testing--debugging)

---

## 1. Pemahaman Laravel Framework

### Q1: Jelaskan struktur MVC yang Anda implementasikan dalam project ini!

**Jawaban:**

Dalam project MedifyID ini, saya mengimplementasikan struktur MVC (Model-View-Controller) sebagai berikut:

**MODEL** (`app/Models/`)
- Bertanggung jawab untuk mengelola data dan business logic
- Berinteraksi dengan database menggunakan Eloquent ORM
- Contoh: `MasterItem.php`, `Kategori.php`

**VIEW** (`resources/views/`)
- Menampilkan data ke user menggunakan Blade templating
- Terstruktur dalam folder: index, form, single
- Contoh: `master_items/index/index.blade.php`

**CONTROLLER** (`app/Http/Controllers/`)
- Menghandle request dari user
- Memproses data dari Model dan mengirim ke View
- Contoh: `MasterItemsController.php`

**Flow MVC:**
```
User → Route → Controller → Model → Database
                    ↓
                View → User
```

**Contoh Implementasi:**

```php
// ROUTE (routes/web.php)
Route::get('/master-items', [MasterItemsController::class, 'index']);

// MODEL (app/Models/MasterItem.php)
class MasterItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['kode', 'nama', 'harga_beli', 'laba', 'supplier', 'jenis', 'photo'];
    
    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
    }
}

// CONTROLLER (app/Http/Controllers/MasterItemsController.php)
public function index()
{
    return view('master_items.index.index');
}

// VIEW (resources/views/master_items/index/index.blade.php)
@extends('layouts.app')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Daftar Master Items</div>
        <div class="card-body">
            @include('master_items.index.filter')
            @include('master_items.index.table')
        </div>
    </div>
</div>
@endsection
```

---

### Q2: Bagaimana cara Anda mendefinisikan relationship antar tabel?

**Jawaban:**

Saya menggunakan Eloquent ORM untuk mendefinisikan relationship. Dalam project ini ada relationship **Many-to-Many** antara Master Item dan Kategori.

**Alasan menggunakan Many-to-Many:**
- Satu Master Item bisa masuk ke beberapa Kategori
- Satu Kategori bisa memiliki beberapa Master Item
- Dibutuhkan pivot table `kategori_item` untuk menghubungkan keduanya

**Implementasi:**

```php
// app/Models/MasterItem.php
class MasterItem extends Model
{
    public function kategoris()
    {
        return $this->belongsToMany(
            Kategori::class,           // Model tujuan
            'kategori_item',           // Tabel pivot
            'master_item_id',          // Foreign key di pivot (dari tabel ini)
            'kategori_id'              // Foreign key di pivot (ke tabel tujuan)
        );
    }
}

// app/Models/Kategori.php
class Kategori extends Model
{
    public function items()
    {
        return $this->belongsToMany(
            MasterItem::class,
            'kategori_item',
            'kategori_id',
            'master_item_id'
        );
    }
}
```

**Cara Menggunakan:**

```php
// Get all kategoris from a Master Item
$item = MasterItem::find(1);
foreach ($item->kategoris as $kategori) {
    echo $kategori->nama;
}

// Get all items from a Kategori
$kategori = Kategori::find(1);
foreach ($kategori->items as $item) {
    echo $item->nama;
}

// Attach kategori to item
$item->kategoris()->attach($kategoriId);

// Sync kategoris (replace all)
$item->kategoris()->sync([1, 2, 3]);
```

---

### Q3: Apa perbedaan `->get()` dan `->paginate()`?

**Jawaban:**

| Aspek | `->get()` | `->paginate()` |
|-------|-----------|----------------|
| **Return** | Collection | LengthAwarePaginator |
| **Data** | Semua data sekaligus | Data per halaman (limit) |
| **Memory** | Besar jika data banyak | Efisien |
| **Performance** | Lambat untuk data besar | Cepat |
| **Pagination** | Tidak ada | Ada (links) |

**Contoh:**

```php
// get() - Mengambil SEMUA data
$items = MasterItem::all(); // atau
$items = MasterItem::where('jenis', 'Obat')->get();
// Return: Illuminate\Support\Collection

// paginate() - Mengambil per halaman
$items = MasterItem::paginate(10);
// Return: Illuminate\Pagination\LengthAwarePaginator
// 10 data per halaman, ada navigasi page 1, 2, 3, dst

// Di Blade
@foreach ($items as $item)
    {{ $item->nama }}
@endforeach

{{ $items->links() }} // Tampilkan pagination
```

**Kapan menggunakan:**
- Gunakan `get()` untuk data sedikit (< 1000 records)
- Gunakan `paginate()` untuk data banyak atau tabel besar

---

## 2. Database & Relationship

### Q4: Mengapa menggunakan Many-to-Many untuk Kategori ↔ Master Item?

**Jawaban:**

Saya menggunakan Many-to-Many karena **requirement bisnis** yang membutuhkan fleksibilitas:

**Skenario Real:**
- Sebuah obat (Master Item) bisa masuk kategori "Obat Bebas" DAN "Obat Generik"
- Kategori "Obat" bisa memiliki banyak item: Paracetamol, Amoxicillin, dll

**Jika One-to-Many:**
```
❌ Satu item hanya bisa punya 1 kategori
❌ Tidak fleksibel untuk klasifikasi multi-dimensi
```

**Dengan Many-to-Many:**
```
✅ Satu item bisa punya banyak kategori
✅ Satu kategori bisa punya banyak item
✅ Fleksibel untuk reporting dan filtering
```

**Struktur Database:**

```sql
-- Tabel: master_items
id | kode  | nama         | ...
1  | 00001 | Paracetamol  | ...

-- Tabel: kategoris
id | kode | nama
1  | OB   | Obat Bebas
2  | OG   | Obat Generik

-- Tabel: kategori_item (PIVOT)
id | kategori_id | master_item_id
1  | 1           | 1              -- Paracetamol → Obat Bebas
2  | 2           | 1              -- Paracetamol → Obat Generik
```

**Implementasi Code:**

```php
// Migration pivot table
Schema::create('kategori_item', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('set null');
    $table->foreignId('master_item_id')->constrained('master_items')->onDelete('set null');
    $table->timestamps();
});

// Di Controller (sync saat save)
public function formSubmit(Request $request, $method, $id = 0)
{
    // ... save master item ...
    
    // Sync kategoris
    if ($request->has('kategoris')) {
        $data_item->kategoris()->sync($request->kategoris);
    } else {
        $data_item->kategoris()->detach();
    }
}
```

---

### Q5: Apa perbedaan `attach()`, `detach()`, dan `sync()`?

**Jawaban:**

Ketiga method ini digunakan untuk mengelola relationship Many-to-Many di pivot table.

**1. `attach()` - Menambah relationship baru**
```php
// Tambah kategori ID 1 ke item
$item->kategoris()->attach(1);

// Tambah beberapa kategori
$item->kategoris()->attach([1, 2, 3]);

// Dengan additional data di pivot
$item->kategoris()->attach([1 => ['priority' => 'high']]);

// SQL: INSERT INTO kategori_item (kategori_id, master_item_id) VALUES (1, 100)
```

**2. `detach()` - Menghapus relationship**
```php
// Hapus kategori ID 1 dari item
$item->kategoris()->detach(1);

// Hapus beberapa kategori
$item->kategoris()->detach([1, 2]);

// Hapus semua relationship
$item->kategoris()->detach();

// SQL: DELETE FROM kategori_item WHERE master_item_id = 100 AND kategori_id = 1
```

**3. `sync()` - Replace semua relationship (yang saya gunakan)**
```php
// Replace semua kategori dengan [1, 2, 3]
// Kategori lain yang tidak ada di array akan di-detach
$item->kategoris()->sync([1, 2, 3]);

// Sync dengan additional data
$item->kategoris()->sync([
    1 => ['priority' => 'high'],
    2 => ['priority' => 'low']
]);

// SQL: DELETE old ones + INSERT new ones
```

**Perbandingan:**

| Method | Behavior | Use Case |
|--------|----------|----------|
| `attach()` | Add only | Tambah kategori tanpa hapus yang lama |
| `detach()` | Remove only | Hapus kategori tertentu |
| `sync()` | Replace all | **Form submit** (replace semua pilihan) |

**Kenapa saya pakai `sync()`?**
Karena saat user submit form, kita ingin **replace semua pilihan kategori** sesuai input terbaru, bukan menambah atau mengurangi satu-satu.

---

### Q6: Kapan menggunakan Soft Delete vs Hard Delete?

**Jawaban:**

**Soft Delete** yang saya gunakan di project ini karena:

**Alasan Bisnis:**
1. **Audit Trail** - Data tidak boleh hilang untuk tracking
2. **Reporting** - Data historis tetap ada untuk laporan
3. **Restore** - Bisa dikembalikan jika salah hapus
4. **Compliance** - Requirement industri kesehatan/farmasi

**Implementasi Soft Delete:**

```php
// Migration
Schema::create('master_items', function (Blueprint $table) {
    $table->id();
    $table->string('kode');
    $table->string('nama');
    // ...
    $table->softDeletes(); // Adds deleted_at column
});

// Model
class MasterItem extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}

// Controller
public function delete($id)
{
    $item = MasterItem::find($id);
    $item->delete(); // SET deleted_at = NOW(), bukan DELETE row
    
    return redirect('master-items')->with('success', 'Data berhasil dihapus (Soft Delete)');
}

public function restore($id)
{
    $item = MasterItem::withTrashed()->findOrFail($id);
    $item->restore(); // SET deleted_at = NULL
    
    return redirect('master-items')->with('success', 'Data berhasil dikembalikan');
}

public function forceDelete($id)
{
    $item = MasterItem::withTrashed()->findOrFail($id);
    
    // Hapus photo juga
    if ($item->photo) {
        Storage::delete('public/photos/' . $item->photo);
    }
    
    $item->forceDelete(); // DELETE row permanen
    
    return redirect('master-items')->with('success', 'Data berhasil dihapus permanen');
}
```

**Query dengan Soft Delete:**

```php
// Default: hanya yang tidak di-delete
MasterItem::all(); // WHERE deleted_at IS NULL

// Include yang sudah di-delete
MasterItem::withTrashed()->get(); // WHERE 1=1

// Hanya yang di-delete
MasterItem::onlyTrashed()->get(); // WHERE deleted_at IS NOT NULL

// Check jika soft deleted
if ($item->trashed()) {
    // Item sudah di-soft delete
}
```

**Kapan Hard Delete?**
- Data sensitif (privacy)
- Data test/dummy
- Compliance dengan "right to be forgotten"
- Data yang benar-benar tidak diperlukan lagi

---

## 3. CRUD Functionality

### Q7: Jelaskan flow dari create data!

**Jawaban:**

Flow create data Master Item:

```
1. User klik "+ Master Items Baru"
        ↓
2. Route: GET /master-items/form/new
        ↓
3. Controller: formView('new')
        ↓
4. Return view: master_items.form.index
        ↓
5. User isi form & submit
        ↓
6. Route: POST /master-items/form/new
        ↓
7. Controller: formSubmit()
   - Validasi input
   - Auto-generate kode
   - Upload photo
   - Save ke database
   - Sync kategori
        ↓
8. Redirect ke /master-items dengan success message
```

**Step-by-Step Code:**

**Step 1: Route**
```php
// routes/web.php
Route::get('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formView']);
Route::post('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formSubmit']);
```

**Step 2: Controller - formView**
```php
public function formView($method, $id = 0)
{
    if ($method == 'new') {
        $item = new MasterItem; // Data kosong untuk create
    } else {
        $item = MasterItem::with('kategoris')->find($id); // Data existing untuk edit
    }
    
    $data['item'] = $item;
    $data['method'] = $method;
    $data['kategoris'] = Kategori::all(); // Untuk dropdown multi-select
    
    return view('master_items.form.index', $data);
}
```

**Step 3: View - Form**
```blade
{{-- resources/views/master_items/form/form.blade.php --}}
<form method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" name="nama" required value="{{ $item->nama ?? '' }}">
    </div>
    
    <div class="form-group">
        <label>Harga Beli</label>
        <input type="number" class="form-control" name="harga_beli" required value="{{ $item->harga_beli ?? '' }}">
    </div>
    
    <div class="form-group">
        <label>Kategori</label>
        <select class="form-control" name="kategoris[]" multiple>
            @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" 
                    {{ isset($item) && $item->kategoris->contains($kat->id) ? 'selected' : '' }}>
                    {{ $kat->nama }}
                </option>
            @endforeach
        </select>
    </div>
    
    <button class="btn btn-primary">Submit</button>
</form>
```

**Step 4: Controller - formSubmit**
```php
public function formSubmit(Request $request, $method, $id = 0)
{
    // 1. Validasi
    $request->validate([
        'nama' => 'required|string|max:255',
        'harga_beli' => 'required|numeric',
        'laba' => 'required|numeric',
        'supplier' => 'required|string',
        'jenis' => 'required|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);
    
    // 2. Prepare data
    if ($method == 'new') {
        $data_item = new MasterItem;
        
        // Auto-generate kode (5 digit padding)
        $last_id = MasterItem::withTrashed()->max('id') ?? 0;
        $kode = $last_id + 1;
        $data_item->kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
    } else {
        $data_item = MasterItem::find($id);
    }
    
    // 3. Handle photo upload
    if ($request->hasFile('photo')) {
        // Hapus photo lama jika edit
        if ($method != 'new' && $data_item->photo) {
            Storage::delete('public/photos/' . $data_item->photo);
        }
        
        // Upload photo baru
        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/photos', $fileName);
        $data_item->photo = $fileName;
    }
    
    // 4. Fill attributes
    $data_item->nama = $request->nama;
    $data_item->harga_beli = $request->harga_beli;
    $data_item->laba = $request->laba;
    $data_item->supplier = $request->supplier;
    $data_item->jenis = $request->jenis;
    
    // 5. Save to database
    $data_item->save();
    
    // 6. Sync kategoris (Many-to-Many)
    if ($request->has('kategoris')) {
        $data_item->kategoris()->sync($request->kategoris);
    } else {
        $data_item->kategoris()->detach();
    }
    
    // 7. Redirect dengan success message
    return redirect('master-items')->with('success', 'Data berhasil disimpan');
}
```

---

### Q8: Bagaimana handling validasi form?

**Jawaban:**

Saya menggunakan **inline validation** di controller dengan `$request->validate()`.

**Implementasi:**

```php
public function formSubmit(Request $request, $method, $id = 0)
{
    // Validasi dengan pesan default Laravel
    $request->validate([
        'nama' => 'required|string|max:255',
        'harga_beli' => 'required|numeric|min:0',
        'laba' => 'required|numeric|min:0|max:100',
        'supplier' => 'required|string|max:255',
        'jenis' => 'required|in:Obat,Alkes,Matkes,Umum,ATK',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'kategoris' => 'nullable|array',
    ]);
    
    // ... lanjut proses save
}
```

**Menampilkan Error di Blade:**

```blade
{{-- resources/views/master_items/form/form.blade.php --}}

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label>Nama</label>
    <input type="text" 
           class="form-control @error('nama') is-invalid @enderror" 
           name="nama" 
           value="{{ old('nama') }}">
    @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Photo</label>
    <input type="file" 
           class="form-control @error('photo') is-invalid @enderror" 
           name="photo"
           accept="image/*">
    @error('photo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

**Custom Error Messages (Optional):**

```php
$request->validate([
    'nama' => 'required|string|max:255',
    'harga_beli' => 'required|numeric',
], [
    'nama.required' => 'Nama barang wajib diisi',
    'harga_beli.required' => 'Harga beli wajib diisi',
    'harga_beli.numeric' => 'Harga beli harus berupa angka',
]);
```

**Form Request Validation (Best Practice untuk kompleks):**

```bash
php artisan make:request StoreMasterItemRequest
```

```php
// app/Http/Requests/StoreMasterItemRequest.php
class StoreMasterItemRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Siapa yang boleh akses
    }
    
    public function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
        ];
    }
    
    public function messages()
    {
        return [
            'nama.required' => 'Nama barang wajib diisi',
        ];
    }
}
```

```php
// Controller
public function store(StoreMasterItemRequest $request)
{
    // Validasi otomatis sebelum masuk controller
    MasterItem::create($request->validated());
}
```

---

### Q9: Apa yang terjadi saat soft delete?

**Jawaban:**

Saat soft delete, data **tidak benar-benar dihapus** dari database, hanya ditandai sebagai "deleted".

**Proses Soft Delete:**

```php
// Controller
public function delete($id)
{
    $item = MasterItem::find($id);
    $item->delete(); // ← Ini yang terjadi:
    
    return redirect('master-items')->with('success', 'Data berhasil dihapus (Soft Delete)');
}
```

**Yang Terjadi di Database:**

```sql
-- BEFORE delete
id | kode  | nama        | deleted_at
1  | 00001 | Paracetamol | NULL

-- AFTER delete (UPDATE, bukan DELETE!)
id | kode  | nama        | deleted_at
1  | 00001 | Paracetamol | 2026-02-25 10:30:00
```

**SQL Equivalent:**
```sql
-- Hard Delete (TIDAK digunakan)
DELETE FROM master_items WHERE id = 1;

-- Soft Delete (yang digunakan)
UPDATE master_items SET deleted_at = NOW() WHERE id = 1;
```

**Impact pada Query:**

```php
// Query default: otomatis exclude soft deleted
MasterItem::all();
// SELECT * FROM master_items WHERE deleted_at IS NULL;

// Include soft deleted
MasterItem::withTrashed()->get();
// SELECT * FROM master_items;

// Only soft deleted
MasterItem::onlyTrashed()->get();
// SELECT * FROM master_items WHERE deleted_at IS NOT NULL;
```

**Check Status:**

```php
$item = MasterItem::find(1);

if ($item->trashed()) {
    // Item sudah di-soft delete
    echo "Item sudah dihapus";
} else {
    // Item masih aktif
    echo "Item masih aktif";
}
```

**Restore:**

```php
public function restore($id)
{
    $item = MasterItem::withTrashed()->findOrFail($id);
    $item->restore(); // SET deleted_at = NULL
    
    return redirect('master-items')->with('success', 'Data berhasil dikembalikan');
}
```

**Force Delete (Hard Delete):**

```php
public function forceDelete($id)
{
    $item = MasterItem::withTrashed()->findOrFail($id);
    
    // Hapus file photo juga
    if ($item->photo) {
        Storage::delete('public/photos/' . $item->photo);
    }
    
    $item->forceDelete(); // DELETE row permanen
    
    return redirect('master-items')->with('success', 'Data berhasil dihapus permanen');
}
```

---

## 4. AJAX & Frontend Integration

### Q10: Bagaimana cara kerja AJAX search?

**Jawaban:**

AJAX search memungkinkan filtering data **tanpa reload page**.

**Flow AJAX Search:**

```
User input filter → Click Filter button
        ↓
JavaScript (jQuery) collect values
        ↓
AJAX request ke server (GET /master-items/search)
        ↓
Controller query database dengan filter
        ↓
Return JSON response
        ↓
JavaScript update DataTable
```

**Implementation:**

**1. View - Filter Form**
```blade
{{-- resources/views/master_items/index/filter.blade.php --}}
<div id="filter-container">
    <label>Kode</label>
    <input type="text" class="form-control" id="filter-kode">
    
    <label>Nama</label>
    <input type="text" class="form-control" id="filter-nama">
    
    <label>Harga Min</label>
    <input type="number" class="form-control" id="filter-harga-min">
    
    <label>Harga Max</label>
    <input type="number" class="form-control" id="filter-harga-max">
    
    <input type="checkbox" id="show_deleted">
    <label>Show Deleted</label>
    
    <button class="btn btn-primary btn-get-data">Filter</button>
</div>
```

**2. View - JavaScript**
```blade
{{-- resources/views/master_items/index/js.blade.php --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var dataTableObj = $('#table').DataTable({
            searching: false, // Disable default search
            order: [[0, 'desc']]
        });
        
        // Load data on page load
        getData();
    });
    
    // Click event untuk tombol Filter
    $('.btn-get-data').click(function() {
        getData();
    });
    
    function getData() {
        var dataTableObj = $('#table').DataTable();
        
        // Collect filter values
        var filter_kode = $('#filter-kode').val();
        var filter_nama = $('#filter-nama').val();
        var filter_harga_min = $('#filter-harga-min').val();
        var filter_harga_max = $('#filter-harga-max').val();
        var show_deleted = $('#show_deleted').is(':checked');
        
        // Clear existing data
        dataTableObj.clear().draw();
        
        // AJAX request
        $.ajax({
            url: '{{ url('master-items/search') }}',
            type: 'GET',
            dataType: 'json',
            data: {
                kode: filter_kode,
                nama: filter_nama,
                hargamin: filter_harga_min,
                hargamax: filter_harga_max,
                show_deleted: show_deleted
            },
            success: function(results) {
                var data = results.data;
                
                // Add each row to DataTable
                $.each(data, function(index, item) {
                    var harga_jual = item.harga_beli + (item.harga_beli * item.laba / 100);
                    
                    var photoHtml = '-';
                    if (item.photo) {
                        photoHtml = `<img src="/storage/photos/${item.photo}" width="50">`;
                    }
                    
                    var actions = item.is_deleted 
                        ? `<button class="btn btn-success btn-restore" data-id="${item.id}">Restore</button>`
                        : `<a href="/master-items/view/${item.kode}" class="btn btn-primary">View</a>`;
                    
                    dataTableObj.row.add([
                        item.kode,
                        item.nama,
                        item.nama_kategori || '-',
                        item.jenis,
                        formatRupiah(item.harga_beli),
                        formatRupiah(harga_jual),
                        item.supplier,
                        photoHtml,
                        actions
                    ]).draw();
                });
            },
            error: function() {
                alert('Terjadi kesalahan server');
            }
        });
    }
    
    // Helper function
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
```

**3. Controller**
```php
public function search(Request $request)
{
    $kode = $request->kode;
    $nama = $request->nama;
    $hargamin = $request->hargamin;
    $hargamax = $request->hargamax;
    $show_deleted = $request->show_deleted;
    
    // Build query
    $data_search = MasterItem::query();
    
    // Apply soft delete filter
    if ($show_deleted == 'true') {
        $data_search = $data_search->withTrashed();
    }
    
    // Apply filters
    if (!empty($kode)) {
        $data_search = $data_search->where('kode', $kode);
    }
    if (!empty($nama)) {
        $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');
    }
    if (!empty($hargamin)) {
        $data_search = $data_search->where('harga_beli', '>=', $hargamin);
    }
    if (!empty($hargamax)) {
        $data_search = $data_search->where('harga_beli', '<=', $hargamax);
    }
    
    // Eager load kategoris
    $data_search = $data_search->with('kategoris')
        ->select('id', 'kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'photo', 'deleted_at')
        ->orderBy('id', 'desc')
        ->get();
    
    // Append additional data
    foreach ($data_search as $item) {
        $item->nama_kategori = $item->kategoris->pluck('nama')->implode(', ');
        $item->is_deleted = $item->trashed();
    }
    
    return response()->json([
        'status' => 200,
        'data' => $data_search
    ]);
}
```

**Keuntungan AJAX Search:**
- ✅ No page reload (better UX)
- ✅ Faster response
- ✅ Can filter multiple fields at once
- ✅ Can include soft deleted data toggle

---

### Q11: Apa keuntungan menggunakan DataTables?

**Jawaban:**

DataTables adalah jQuery plugin yang memberikan fitur interaktif pada tabel HTML.

**Keuntungan:**

1. **Sorting** - Click header untuk sort ascending/descending
2. **Pagination** - Otomatis paginate data besar
3. **Search** - Built-in search functionality
4. **Responsive** - Mobile-friendly
5. **Export** - Bisa export ke Excel, PDF, CSV
6. **Customizable** - Banyak option konfigurasi

**Implementasi:**

```blade
{{-- Table HTML --}}
<table id="table" class="table table-striped">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Jenis</th>
            <th>Harga Beli</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data will be populated by AJAX -->
    </tbody>
</table>

{{-- JavaScript Initialization --}}
<script>
    $(document).ready(function() {
        $('#table').DataTable({
            // Options
            searching: false,      // Disable default search (kita pakai custom filter)
            ordering: true,        // Enable sorting
            order: [[0, 'desc']],  // Default sort by first column (descending)
            pageLength: 10,        // Items per page
            lengthChange: true,    // Allow user to change page length
            responsive: true,      // Enable responsive mode
            columnDefs: [
                { width: "10%", targets: 5 }  // Set column width
            ]
        });
    });
</script>
```

**Advanced Features:**

```javascript
// Server-side processing (untuk data sangat besar)
$('#table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/master-items/data',
    columns: [
        { data: 'kode' },
        { data: 'nama' },
        { data: 'kategori' }
    ]
});

// Export buttons
$('#table').DataTable({
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ]
});
```

**Kenapa saya pakai DataTables:**
- ✅ User-friendly (sorting, pagination built-in)
- ✅ Handle large data efficiently
- ✅ Easy integration dengan AJAX
- ✅ Professional look & feel
- ✅ Time-saving (tidak perlu buat pagination manual)

---

## 5. Export Functionality

### Q12: Package apa yang digunakan untuk export?

**Jawaban:**

Saya menggunakan 2 package untuk export:

**1. Export Excel - Maatwebsite Excel**

```bash
composer require maatwebsite/excel
```

**Config:**
```php
// config/excel.php (auto-generated setelah install)
```

**Implementation:**

```php
// app/Exports/MasterItemsExport.php
namespace App\Exports;

use App\Models\MasterItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return MasterItem::with('kategoris')->get();
    }
    
    public function headings(): array
    {
        return [
            'Kode',
            'Nama',
            'Kategori',
            'Harga Beli',
            'Laba (%)',
            'Harga Jual',
            'Supplier',
            'Jenis'
        ];
    }
    
    public function map($item): array
    {
        $harga_jual = $item->harga_beli + ($item->harga_beli * $item->laba / 100);
        
        return [
            $item->kode,
            $item->nama,
            $item->kategoris->pluck('nama')->implode(', '),
            $item->harga_beli,
            $item->laba . '%',
            round($harga_jual),
            $item->supplier,
            $item->jenis
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
        ];
    }
}

// Controller
public function downloadExcel()
{
    return Excel::download(new MasterItemsExport, 'master-items.xlsx');
}
```

**2. Export PDF - DomPDF (barryvdh/laravel-dompdf)**

```bash
composer require barryvdh/laravel-dompdf
```

**Implementation:**

```php
// Controller
use Barryvdh\DomPDF\Facade\Pdf;

public function downloadPdf($id)
{
    $data = Kategori::with('items')->findOrFail($id);
    $time = date('d/m/Y H:i:s');
    
    $pdf = Pdf::loadView('kategoris.single.pdf', compact('data', 'time'));
    
    return $pdf->download('Kategori-' . $data->kode . '.pdf');
}
```

```blade
{{-- resources/views/kategoris/single/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kategori - {{ $data->kode }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Detail Kategori</h2>
    <p><strong>Kode:</strong> {{ $data->kode }}</p>
    <p><strong>Nama:</strong> {{ $data->nama }}</p>
    <p><strong>Tanggal Cetak:</strong> {{ $time }}</p>
    
    <h3>Daftar Item</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Harga Beli</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->items as $item)
            <tr>
                <td>{{ $item->kode }}</td>
                <td>{{ $item->nama }}</td>
                <td>Rp {{ number_format($item->harga_beli) }}</td>
                <td>{{ $item->jenis }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

**Perbandingan:**

| Feature | Maatwebsite Excel | DomPDF |
|---------|-------------------|--------|
| **Format** | .xlsx, .csv | .pdf |
| **Data Type** | Structured data | Document/Report |
| **Styling** | Limited | Full HTML/CSS |
| **Use Case** | Data export for analysis | Report for printing |

---

## 6. File Upload Handling

### Q13: Bagaimana validasi file upload?

**Jawaban:**

Validasi file upload sangat penting untuk keamanan.

**Validation Rules:**

```php
$request->validate([
    'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
]);
```

**Penjelasan Rules:**

| Rule | Meaning |
|------|---------|
| `nullable` | Boleh kosong (optional) |
| `image` | Harus berupa gambar |
| `mimes:jpeg,png,jpg,gif` | Format yang diperbolehkan |
| `max:2048` | Max size 2MB (in KB) |

**Custom Error Messages:**

```php
$request->validate([
    'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
], [
    'photo.image' => 'File harus berupa gambar',
    'photo.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
    'photo.max' => 'Ukuran gambar maksimal 2MB'
]);
```

**Upload Implementation:**

```php
public function formSubmit(Request $request, $method, $id = 0)
{
    // ... validasi ...
    
    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        
        // Generate unique filename
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Store file
        $file->storeAs('public/photos', $fileName);
        
        // Delete old photo if editing
        if ($method != 'new' && $data_item->photo) {
            Storage::delete('public/photos/' . $data_item->photo);
        }
        
        $data_item->photo = $fileName;
    }
    
    // ... save ...
}
```

**Display Uploaded File:**

```blade
{{-- Form - Preview existing photo --}}
@if(isset($item->photo) && $item->photo)
    <div class="mt-2">
        <label>Photo Saat Ini:</label>
        <img src="{{ asset('storage/photos/' . $item->photo) }}" 
             width="150" 
             style="object-fit: cover; border-radius: 5px;">
    </div>
@endif

{{-- Table - Display thumbnail --}}
@if($item->photo)
    <img src="{{ asset('storage/photos/' . $item->photo) }}" 
         width="50" 
         height="50" 
         style="object-fit: cover; border-radius: 5px;">
@else
    -
@endif
```

**Storage Link (Penting!):**

```bash
php artisan storage:link
```

Ini membuat symbolic link dari `public/storage` ke `storage/app/public` sehingga file bisa diakses via web.

---

## 7. Code Quality & Best Practices

### Q14: Apa prinsip DRY dan bagaimana implementasinya?

**Jawaban:**

**DRY = Don't Repeat Yourself**

Prinsip: Setiap logika/kode harus ditulis sekali saja, tidak boleh duplikasi.

**Contoh Violation (Buruk):**

```php
// BAD: Kode duplikat di 2 method
public function create()
{
    $item = new MasterItem;
    $kategoris = Kategori::all();
    return view('master_items.form', compact('item', 'kategoris'));
}

public function edit($id)
{
    $item = MasterItem::find($id);
    $kategoris = Kategori::all();
    return view('master_items.form', compact('item', 'kategoris'));
}
```

**Contoh DRY (Baik) - Yang Saya Implementasikan:**

```php
// GOOD: Satu method untuk create & edit
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
```

**DRY di View:**

```blade
{{-- BAD: Duplikat form di create dan edit --}}
{{-- create.blade.php --}}
<form method="POST">
    @csrf
    <input name="nama" value="">
    <input name="harga_beli" value="">
    <button>Save</button>
</form>

{{-- edit.blade.php --}}
<form method="POST">
    @csrf
    <input name="nama" value="{{ $item->nama }}">
    <input name="harga_beli" value="{{ $item->harga_beli }}">
    <button>Update</button>
</form>

{{-- GOOD: Satu partial form --}}
{{-- _form.blade.php --}}
<form method="POST">
    @csrf
    <input name="nama" value="{{ $item->nama ?? '' }}">
    <input name="harga_beli" value="{{ $item->harga_beli ?? '' }}">
    <button>{{ $method == 'new' ? 'Save' : 'Update' }}</button>
</form>
```

**DRY di Model (Eager Loading):**

```php
// BAD: Harus tulis ->with() setiap saat
$items = MasterItem::with('kategoris')->get();
$item = MasterItem::with('kategoris')->find($id);

// GOOD: Set default di model
class MasterItem extends Model
{
    protected $with = ['kategoris']; // Always load kategoris
}

// Sekarang tidak perlu ->with() lagi
$items = MasterItem::all();
$item = MasterItem::find($id);
```

**Benefits of DRY:**
- ✅ Easier to maintain
- ✅ Less bugs (change once, affect all)
- ✅ More readable
- ✅ Less code to write

---

### Q15: Bagaimana mencegah SQL Injection di Laravel?

**Jawaban:**

Laravel sudah memiliki proteksi SQL Injection built-in melalui **Eloquent ORM** dan **Query Builder**.

**Aman (Menggunakan Eloquent):**

```php
// ✅ SAFE - Parameter binding otomatis
MasterItem::where('nama', $request->nama)->get();

// ✅ SAFE - Array binding
DB::table('master_items')
    ->where('nama', '=', $request->nama)
    ->get();

// ✅ SAFE - Prepared statement
DB::select('SELECT * FROM master_items WHERE nama = ?', [$request->nama]);
```

**Bahaya (Raw SQL tanpa binding):**

```php
// ❌ DANGEROUS - SQL Injection vulnerability
$nama = $request->nama;
DB::select("SELECT * FROM master_items WHERE nama = '$nama'");

// Attack: Input nama = ' OR '1'='1
// Result: SELECT * FROM master_items WHERE nama = '' OR '1'='1'
// Returns ALL records!
```

**Implementasi Aman di Project:**

```php
public function search(Request $request)
{
    $kode = $request->kode;
    $nama = $request->nama;
    
    // ✅ SAFE - Query Builder dengan parameter binding
    $data_search = MasterItem::query();
    
    if (!empty($kode)) {
        $data_search = $data_search->where('kode', $kode); // Bound parameter
    }
    
    if (!empty($nama)) {
        $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%'); // Bound parameter
    }
    
    $data = $data_search->get();
    
    return response()->json(['status' => 200, 'data' => $data]);
}
```

**Additional Security Measures:**

```php
// 1. Validation
$request->validate([
    'nama' => 'required|string|max:255', // Prevent long strings
    'id' => 'required|integer|exists:master_items,id' // Ensure valid ID
]);

// 2. Type casting
$id = (int) $request->id;

// 3. Using route model binding (safest)
public function show(MasterItem $item) // Auto-resolve & validate
{
    return view('show', compact('item'));
}

// 4. Escape output di Blade
{{ $userInput }} {{-- Auto-escaped --}}
{!! $trustedHtml !!} {{-- Only if trusted --}}
```

**CSRF Protection (Already built-in):**

```blade
<form method="POST">
    @csrf <!-- Token CSRF otomatis -->
    <!-- form fields -->
</form>
```

---

## 8. Testing & Debugging

### Q16: Bagaimana cara Anda testing fitur yang dibuat?

**Jawaban:**

Saya melakukan **manual testing** dengan skenario berikut:

**Testing Checklist:**

**1. Create Data**
```
✅ Form tampil dengan field kosong
✅ Validasi error jika field wajib kosong
✅ Upload photo berhasil
✅ Pilih kategori (multi-select) berhasil
✅ Data tersimpan ke database
✅ Kode auto-generate (00001, 00002, dst)
✅ Redirect dengan success message
```

**2. Read/List Data**
```
✅ Data tampil di tabel
✅ Filter by kode berhasil
✅ Filter by nama berhasil (LIKE search)
✅ Filter by harga min-max berhasil
✅ Toggle "Show Deleted" berhasil
✅ Pagination bekerja
✅ Sorting by column bekerja
```

**3. Update Data**
```
✅ Form edit menampilkan data existing
✅ Photo existing tampil preview
✅ Update photo (hapus lama, upload baru)
✅ Update data berhasil
✅ Sync kategori berhasil
```

**4. Delete Data**
```
✅ Soft delete berhasil (data masih ada, deleted_at terisi)
✅ Data tidak tampil di list default
✅ Data tampil jika "Show Deleted" checked
✅ Restore berhasil (deleted_at jadi NULL)
✅ Force delete berhasil (data hilang permanen)
✅ Photo terhapus saat force delete
```

**5. Export**
```
✅ Download Excel berhasil
✅ Data di Excel sesuai dengan database
✅ Download PDF berhasil
✅ PDF format rapi
```

**6. Edge Cases**
```
✅ Input special characters (', ", <, >, &)
✅ Input sangat panjang
✅ Upload file besar (>2MB) - ditolak
✅ Upload format salah (.exe, .pdf) - ditolak
✅ Delete data yang punya relasi kategori
✅ Search dengan keyword tidak ditemukan
```

**Debugging Tools:**

```php
// 1. dd() - Dump and die
dd($item);

// 2. dump()
dump($item);

// 3. Log
Log::info('Item created', ['id' => $item->id]);

// 4. Laravel Debugbar (package)
composer require barryvdh/laravel-debugbar

// 5. Tinker for testing
php artisan tinker
>>> App\Models\MasterItem::count()
>>> App\Models\MasterItem::first()
```

**Browser DevTools:**

```
✅ Console - Check JavaScript errors
✅ Network - Check AJAX requests
✅ Elements - Inspect HTML
✅ Application - Check localStorage, cookies
```

---

### Q17: Apa yang dilakukan jika menemukan bug?

**Jawaban:**

**Step-by-Step Debugging:**

**1. Reproduce the Bug**
```
- Catat langkah untuk reproduce
- Pastikan bug konsisten terjadi
- Check apakah bug terjadi di environment lain
```

**2. Check Error Logs**
```bash
# Laravel log
tail -f storage/logs/laravel.log

# PHP error log
tail -f /var/log/php/error.log
```

**3. Enable Debug Mode**
```env
# .env
APP_DEBUG=true
APP_ENV=local
```

**4. Isolate the Problem**
```php
// Add logging
Log::info('Before save', ['data' => $request->all()]);

// Check query
DB::enableQueryLog();
// ... code ...
dd(DB::getQueryLog());
```

**5. Fix and Test**
```
- Buat fix
- Test skenario yang sama
- Test skenario lain (regression test)
- Commit dengan message jelas
```

**Contoh Real Bug Fix:**

```php
// BUG: Kategori tidak tersimpan saat create Master Item
// Cause: Forgot to sync relationship

// BEFORE (Wrong)
public function formSubmit(Request $request, $method, $id = 0)
{
    // ... save item ...
    // Missing: sync kategoris!
    return redirect('master-items');
}

// AFTER (Fixed)
public function formSubmit(Request $request, $method, $id = 0)
{
    // ... save item ...
    
    // Sync kategoris
    if ($request->has('kategoris')) {
        $data_item->kategoris()->sync($request->kategoris);
    } else {
        $data_item->kategoris()->detach();
    }
    
    return redirect('master-items');
}
```

---

## 🎯 Pertanyaan Bonus

### Q18: Jika diberi waktu tambahan, fitur apa yang akan ditambahkan?

**Jawaban:**

**Priority 1 (High):**
1. **Form Request Validation** - Lebih clean, reusable
2. **Unit Tests** - PHPUnit untuk automated testing
3. **Default Eager Loading** - Add `$with` di model

**Priority 2 (Medium):**
4. **Restore & Force Delete untuk Kategori** - Konsistensi dengan Master Item
5. **Excel Export untuk Kategori** - Lengkap seperti Master Item
6. **Photo Upload untuk Kategori** - Visual identification

**Priority 3 (Nice to Have):**
7. **Search Scopes** - Reusable query scopes
8. **API Endpoints** - RESTful API
9. **Activity Log** - Audit trail
10. **User Roles** - Admin, Staff permissions

---

## 📝 Tips Interview

### Do's ✅
- Jelaskan dengan contoh code
- Tunjukkan file yang relevan
- Jelaskan "kenapa" bukan hanya "apa"
- Jujur jika tidak tahu
- Tunjukkan antusiasme belajar

### Don'ts ❌
- Jangan menghafal code
- Jangan asal jawab
- Jangan menyalahkan tools/framework
- Jangan panik jika ada yang ditanyakan

---

**Good Luck untuk Interview!** 🍀

**Last Updated:** 25 Februari 2026  
**Version:** 1.0
