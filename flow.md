# Flow Development - MedifyID Test Project

## 📊 Analisa Struktur Project

### Modul yang Sudah Ada
1. **Master Item** - Modul utama untuk manajemen barang/item
2. **Kategori** - Modul untuk mengkategorikan item (Many-to-Many relationship)

### Struktur Database
```
master_items
├── id
├── kode (unique, auto-generated)
├── nama
├── harga_beli
├── laba (persen)
├── supplier
├── jenis
├── photo
├── timestamps
└── soft_deletes

kategoris
├── id
├── kode (unique)
├── nama
├── timestamps
└── soft_deletes

kategori_item (pivot table)
├── id
├── kategori_id (FK → kategoris)
├── master_item_id (FK → master_items)
└── timestamps
```

### Relationship
- **Kategori ↔ MasterItem**: Many-to-Many (via `kategori_item`)
- Satu kategori bisa memiliki banyak item
- Satu item bisa masuk ke banyak kategori

---

## 🚀 Efficient Laravel Development Flow

### 1. Create Model dengan Migration, Controller, dan Resource
```bash
# Single command untuk membuat Model + Migration + Controller + Request
php artisan make:model Kategori -mcr
php artisan make:model MasterItem -mcr
```

**Flags explanation:**
- `-m` = Create migration file
- `-c` = Create controller
- `-r` = Create form request for validation
- `-R` = Create resource (API resource)
- `-f` = Create factory
- `-s` = Create seeder

### 2. Define Migration Schema
Edit migration file di `database/migrations/xxxx_create_table_name.php`:

```php
public function up()
{
    // Table: kategoris
    Schema::create('kategoris', function (Blueprint $table) {
        $table->id();
        $table->string('kode')->unique();
        $table->string('nama');
        $table->timestamps();
        $table->softDeletes();
    });

    // Pivot Table: kategori_item
    Schema::create('kategori_item', function (Blueprint $table) {
        $table->id();
        $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('cascade');
        $table->foreignId('master_item_id')->constrained('master_items')->onDelete('cascade');
        $table->timestamps();
    });
}
```

### 3. Setup Model Relationships & Eager Loading

**Kategori Model** (`app/Models/Kategori.php`):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama', 'kode'];

    // Eager Loading - Load relationships by default
    protected $with = ['items'];

    // Relationship: Many-to-Many
    public function items()
    {
        return $this->belongsToMany(MasterItem::class, 'kategori_item', 'kategori_id', 'master_item_id');
    }
}
```

**MasterItem Model** (`app/Models/MasterItem.php`):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'harga_beli',
        'laba',
        'supplier',
        'jenis',
        'photo'
    ];

    // Eager Loading - Load relationships by default
    protected $with = ['kategoris'];

    // Relationship: Many-to-Many
    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
    }
}
```

### 4. Run Migration
```bash
php artisan migrate
```

### 5. Create Controller Methods (Pattern dari Project)

**KategoriController** (`app/Http/Controllers/KategoriController.php`):
```php
<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\MasterItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KategoriExport;

class KategoriController extends Controller
{
    // View: Halaman index
    public function index()
    {
        return view('kategoris.index.index');
    }

    // AJAX: Search & filter data
    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;

        $data_search = Kategori::query();

        if (!empty($kode)) $data_search = $data_search->where('kode', 'LIKE', '%' . $kode . '%');
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        // Eager loading relationship
        $data_search = $data_search->with('items')
            ->orderBy('id', 'desc')
            ->get();

        // Append additional data
        foreach ($data_search as $item) {
            $item->jumlah_item = $item->items->count();
        }

        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    // View: Form create/edit
    public function formView($method, $id = 0)
    {
        $item = ($method == 'new') ? new Kategori : Kategori::findOrFail($id);

        return view('kategoris.form.index', [
            'item' => $item,
            'method' => $method
        ]);
    }

    // Process: Submit form (create/update)
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

    // View: Single detail view
    public function singleView($id)
    {
        // Eager loading relationship
        $data = Kategori::with('items')->findOrFail($id);
        return view('kategoris.single.index', compact('data'));
    }

    // Process: Delete (soft delete)
    public function delete($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect('/kategoris')->with('success', 'Data Kategori berhasil dihapus');
    }

    // Export: PDF
    public function downloadPdf($id)
    {
        $data = Kategori::with('items')->findOrFail($id);
        $time = date('d/m/Y H:i:s');

        $pdf = Pdf::loadView('kategoris.single.pdf', compact('data', 'time'));

        return $pdf->download('Kategori-' . $data->kode . '.pdf');
    }

    // Export: Excel
    public function downloadExcel()
    {
        return Excel::download(new KategoriExport, 'kategoris.xlsx');
    }
}
```

### 6. Create Form Request Validation (Optional - jika ingin terpisah)
```bash
php artisan make:request StoreKategoriRequest
php artisan make:request UpdateKategoriRequest
```

### 7. Setup Routes
Edit `routes/web.php`:

```php
// Kategori Routes
Route::get('/kategoris', [App\Http\Controllers\KategoriController::class, 'index']);
Route::get('/kategoris/search', [App\Http\Controllers\KategoriController::class, 'search']);
Route::get('/kategoris/form/{method}/{id?}', [App\Http\Controllers\KategoriController::class, 'formView']);
Route::post('/kategoris/form/{method}/{id?}', [App\Http\Controllers\KategoriController::class, 'formSubmit']);
Route::get('/kategoris/view/{id}', [App\Http\Controllers\KategoriController::class, 'singleView']);
Route::get('/kategoris/download-pdf/{id}', [App\Http\Controllers\KategoriController::class, 'downloadPdf']);
Route::get('/kategoris/download-excel', [App\Http\Controllers\KategoriController::class, 'downloadExcel']);
Route::post('/kategoris/delete/{id}', [App\Http\Controllers\KategoriController::class, 'delete']);
```

### 8. Create Views Structure
```
resources/views/kategoris/
├── index/
│   ├── index.blade.php    (main view)
│   ├── filter.blade.php   (filter form)
│   ├── table.blade.php    (data table)
│   └── js.blade.php       (AJAX & DataTable)
├── form/
│   ├── index.blade.php    (form wrapper)
│   └── form.blade.php     (form content)
└── single/
    ├── index.blade.php    (detail view)
    └── pdf.blade.php      (PDF template)
```

### 9. Sync Many-to-Many Relationship (MasterItem Form)
Edit `resources/views/master_items/form/form.blade.php`:

```blade
<div class="form-group mb-3">
    <label for="kategoris">Kategori</label>
    <select class="form-control" name="kategoris[]" id="kategoris" multiple>
        @foreach($kategoris as $kat)
            @php
                $selected = false;
                if(isset($item) && $item->kategoris) {
                    $selected = $item->kategoris->contains($kat->id);
                }
            @endphp
            <option value="{{ $kat->id }}" {{ $selected ? 'selected' : '' }}>
                {{ $kat->nama }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">Tahan Ctrl untuk memilih lebih dari satu</small>
</div>
```

**Controller sync** (`MasterItemsController@formSubmit`):
```php
// Sync Kategoris
if ($request->has('kategoris')) {
    $data_item->kategoris()->sync($request->kategoris);
} else {
    $data_item->kategoris()->detach();
}
```

---

## 📊 Relationship Types & Eager Loading

### Many to Many (Yang Digunakan di Project)
```php
// Kategori Model
public function items()
{
    return $this->belongsToMany(MasterItem::class, 'kategori_item', 'kategori_id', 'master_item_id');
}

// MasterItem Model
public function kategoris()
{
    return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
}

// Usage dengan eager loading
$kategoris = Kategori::with('items')->get();
$items = MasterItem::with('kategoris')->get();
```

### Eager Loading di Controller
```php
// Load single relationship
$item = MasterItem::with('kategoris')->find($id);

// Load multiple relationships
$item = MasterItem::with(['kategoris', 'supplier'])->get();

// Conditional eager loading
$item = MasterItem::with(['kategoris' => function ($query) {
    $query->where('active', true);
}])->get();
```

### Default Eager Loading (di Model)
```php
class MasterItem extends Model
{
    // Selalu load relationships ini setiap query
    protected $with = ['kategoris'];
}
```

### Sync Many-to-Many Relationship
```php
// Attach (tambah relationship)
$item->kategoris()->attach($kategoriId);

// Detach (hapus relationship)
$item->kategoris()->detach($kategoriId);

// Sync (replace semua relationship)
$item->kategoris()->sync([1, 2, 3]);

// Sync dengan additional pivot data
$item->kategoris()->sync([1 => ['priority' => 'high'], 2, 3]);
```

---

## 🎯 Best Practices (Dari Project Ini)

1. **Gunakan `-mcr` flag** untuk membuat Model + Migration + Controller + Request sekaligus
2. **Setup `$with` di model** untuk eager loading default
3. **Pisahkan view menjadi partials**: `filter.blade.php`, `table.blade.php`, `js.blade.php`
4. **Gunakan AJAX search** untuk filtering data tanpa reload
5. **Gunakan DataTables** untuk display data yang interaktif
6. **Gunakan Soft Deletes** untuk data yang tidak boleh hilang permanen
7. **Gunakan `sync()`** untuk many-to-many relationship di form
8. **Export Excel & PDF** menggunakan package yang sudah ada
9. **Auto-generate kode** dengan format padding (contoh: 00001)
10. **Upload file** dengan Storage facade

---

## 📝 Quick Reference Commands

```bash
# Generate Model dengan semua kebutuhan dasar
php artisan make:model NamaModel -mcfrs

# Generate hanya migration
php artisan make:migration create_table_name

# Generate hanya controller
php artisan make:controller NamaController --resource

# Generate hanya request
php artisan make:request StoreNamaRequest

# Generate hanya factory
php artisan make:factory NamaFactory --model=NamaModel

# Generate hanya seeder
php artisan make:seeder NamaSeeder

# Generate export class
php artisan make:export NamaExport --model=NamaModel

# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Reset & re-run migration
php artisan migrate:reset && php artisan migrate

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Troubleshooting

### Migration Error
```bash
# Reset migration
php artisan migrate:reset

# Re-run migration
php artisan migrate

# Fresh migration (hapus semua data)
php artisan migrate:fresh --seed
```

### Relationship Error
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Regenerate autoload
composer dump-autoload
```

### Storage Link (untuk upload foto)
```bash
php artisan storage:link
```
