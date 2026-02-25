# Task List - MedifyID Test Project (Detailed)

## 📊 Project Overview

**Project Name**: MedifyID Test - Inventory Management System  
**Framework**: Laravel 9.x  
**Database**: MySQL  
**PHP Version**: 8.2+

### Modul yang Sudah Ada
| No | Modul | Status | Relationship | Fitur Utama |
|----|-------|--------|--------------|-------------|
| 1 | Master Item | ✅ Complete | Many-to-Many with Kategori | CRUD, Search, Export Excel, Soft Delete, Photo Upload, Auto Code |
| 2 | Kategori | ✅ Complete | Many-to-Many with Master Item | CRUD, Search, Export PDF, Soft Delete |
| 3 | User (Auth) | ✅ Complete | - | Laravel UI Auth |

---

## 🗄️ Database Schema Detail

### 1. Table: `master_items`

**File Migration**: `database/migrations/2022_11_05_005605_create_master_items_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `id` | BIGINT | - | ❌ | AUTO_INCREMENT | PRIMARY KEY | Auto increment |
| `kode` | VARCHAR | 255 | ❌ | - | UNIQUE | Kode barang (auto-generate 5 digit) |
| `nama` | VARCHAR | 255 | ❌ | - | - | Nama barang |
| `harga_beli` | INTEGER | - | ❌ | - | - | Harga beli (Rp) |
| `laba` | INTEGER | - | ❌ | - | - | Persentase laba (%) |
| `supplier` | VARCHAR | 255 | ❌ | - | - | Nama supplier |
| `jenis` | VARCHAR | 255 | ❌ | - | - | Jenis barang (Obat, Alkes, Matkes, Umum, ATK) |
| `photo` | VARCHAR | 255 | ✅ | NULL | - | Nama file photo |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |
| `updated_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu diupdate |
| `deleted_at` | TIMESTAMP | - | ✅ | NULL | INDEX (soft delete) | Soft delete timestamp |

**Migration Code**:
```php
Schema::create('master_items', function (Blueprint $table) {
    $table->id();
    $table->string('kode')->unique();
    $table->string('nama');
    $table->integer('harga_beli');
    $table->integer('laba');
    $table->string('supplier');
    $table->string('jenis');
    $table->timestamps();
    $table->softDeletes();
});

// Add photo column (separate migration)
Schema::table('master_items', function (Blueprint $table) {
    $table->string('photo')->nullable()->after('jenis');
});
```

**Model**: `app/Models/MasterItem.php`
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
        'photo',
    ];

    // Eager loading default (RECOMMENDED: Add this)
    // protected $with = ['kategoris'];

    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
    }
}
```

**Validation Rules**:
```php
[
    'nama' => 'required|string|max:255',
    'harga_beli' => 'required|numeric|min:0',
    'laba' => 'required|numeric|min:0|max:100',
    'supplier' => 'required|string|max:255',
    'jenis' => 'required|string|in:Obat,Alkes,Matkes,Umum,ATK',
    'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]
```

---

### 2. Table: `kategoris`

**File Migration**: `database/migrations/2026_02_21_064740_create_kategoris_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `id` | BIGINT | - | ❌ | AUTO_INCREMENT | PRIMARY KEY | Auto increment |
| `kode` | VARCHAR | 50 | ❌ | - | UNIQUE | Kode kategori (input manual) |
| `nama` | VARCHAR | 255 | ❌ | - | - | Nama kategori |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |
| `updated_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu diupdate |
| `deleted_at` | TIMESTAMP | - | ✅ | NULL | INDEX (soft delete) | Soft delete timestamp |

**Migration Code**:
```php
Schema::create('kategoris', function (Blueprint $table) {
    $table->id();
    $table->string('kode')->unique();
    $table->string('nama');
    $table->timestamps();
    $table->softDeletes();
});
```

**Model**: `app/Models/Kategori.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
    ];

    // Eager loading default (RECOMMENDED: Add this)
    // protected $with = ['items'];

    public function items()
    {
        return $this->belongsToMany(MasterItem::class, 'kategori_item', 'kategori_id', 'master_item_id');
    }
}
```

**Validation Rules**:
```php
[
    'kode' => 'required|string|max:50|unique:kategoris,kode,' . ($id ?: 'NULL') . ',id',
    'nama' => 'required|string|max:255',
]
```

---

### 3. Table: `kategori_item` (Pivot Table)

**File Migration**: `database/migrations/2026_02_21_064748_create_kategori_item_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `id` | BIGINT | - | ❌ | AUTO_INCREMENT | PRIMARY KEY | Auto increment |
| `kategori_id` | BIGINT | - | ✅ | NULL | FOREIGN KEY | FK → kategoris.id (ON DELETE SET NULL) |
| `master_item_id` | BIGINT | - | ✅ | NULL | FOREIGN KEY | FK → master_items.id (ON DELETE SET NULL) |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |
| `updated_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu diupdate |

**Migration Code**:
```php
Schema::create('kategori_item', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->onDelete('set null');
    $table->foreignId('master_item_id')->nullable()->constrained('master_items')->onDelete('set null');
    $table->timestamps();
});
```

**Relationship Usage**:
```php
// Attach kategori to item
$item->kategoris()->attach($kategoriId);

// Detach kategori from item
$item->kategoris()->detach($kategoriId);

// Sync (replace all) kategoris
$item->kategoris()->sync([1, 2, 3]);

// Get all items in kategori
$kategori->items;

// Get all kategoris of item
$item->kategoris;
```

---

### 4. Table: `users` (Laravel Auth)

**File Migration**: `database/migrations/2014_10_12_000000_create_users_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `id` | BIGINT | - | ❌ | AUTO_INCREMENT | PRIMARY KEY | Auto increment |
| `name` | VARCHAR | 255 | ❌ | - | - | Nama user |
| `email` | VARCHAR | 255 | ❌ | - | UNIQUE | Email (untuk login) |
| `email_verified_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu verifikasi email |
| `password` | VARCHAR | 255 | ❌ | - | - | Hashed password |
| `remember_token` | VARCHAR | 100 | ✅ | NULL | - | Remember me token |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |
| `updated_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu diupdate |

---

### 5. Table: `password_resets`

**File Migration**: `database/migrations/2014_10_12_100000_create_password_resets_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `email` | VARCHAR | 255 | ❌ | - | INDEX | Email user |
| `token` | VARCHAR | 255 | ❌ | - | - | Reset token |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |

---

### 6. Table: `personal_access_tokens` (Laravel Sanctum)

**File Migration**: `database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`

| Kolom | Tipe Data | Length | Nullable | Default | Index | Keterangan |
|-------|-----------|--------|----------|---------|-------|------------|
| `id` | BIGINT | - | ❌ | AUTO_INCREMENT | PRIMARY KEY | Auto increment |
| `tokenable_type` | VARCHAR | 255 | ❌ | - | INDEX | Morph type |
| `tokenable_id` | BIGINT | - | ❌ | - | INDEX | Morph ID |
| `name` | VARCHAR | 255 | ❌ | - | - | Token name |
| `token` | VARCHAR | 64 | ❌ | - | UNIQUE | Token hash |
| `abilities` | TEXT | - | ✅ | NULL | - | Token abilities |
| `last_used_at` | TIMESTAMP | - | ✅ | NULL | - | Last use timestamp |
| `created_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu dibuat |
| `updated_at` | TIMESTAMP | - | ✅ | NULL | - | Waktu diupdate |

---

## ✅ Task Checklist Detail

### MODULE: MASTER ITEM

#### A. Database & Model
- [x] **Create Migration**
  - File: `database/migrations/2022_11_05_005605_create_master_items_table.php`
  - Command: `php artisan make:migration create_master_items_table`
  
- [x] **Add Photo Column**
  - File: `database/migrations/2026_02_21_064740_add_photo_to_master_items_table.php`
  - Command: `php artisan make:migration add_photo_to_master_items_table --table=master_items`
  
- [x] **Run Migration**
  ```bash
  php artisan migrate
  ```

- [x] **Create Model**
  - File: `app/Models/MasterItem.php`
  - Command: `php artisan make:model MasterItem`
  
- [x] **Model Configuration**
  - [x] Add `use HasFactory, SoftDeletes`
  - [x] Define `$fillable` array
  - [x] Define relationship `kategoris()` (Many-to-Many)
  - [ ] **TODO**: Add `protected $with = ['kategoris']` for eager loading

#### B. Controller
- [x] **Create Controller**
  - File: `app/Http/Controllers/MasterItemsController.php`
  - Command: `php artisan make:controller MasterItemsController`
  
- [x] **Implement Methods**:
  - [x] `index()` - Return view `master_items.index.index`
  - [x] `search(Request $request)` - AJAX search & filter
    - Parameters: `kode`, `nama`, `hargamin`, `hargamax`, `show_deleted`
    - Returns: JSON with status 200 and data array
  - [x] `formView($method, $id)` - Show create/edit form
    - Parameters: `$method` ('new' or 'edit'), `$id` (optional)
    - Returns: View with item data and kategoris list
  - [x] `formSubmit(Request $request, $method, $id)` - Process form submission
    - Validation: nama, harga_beli, laba, supplier, jenis, photo
    - Auto-generate kode (5 digit padding)
    - Handle photo upload
    - Sync kategoris relationship
  - [x] `singleView($kode)` - Show detail view by kode
  - [x] `delete($id)` - Soft delete
  - [x] `restore($id)` - Restore soft deleted item
  - [x] `forceDelete($id)` - Permanent delete + delete photo file
  - [x] `downloadExcel()` - Export to Excel using Maatwebsite Excel
  - [x] `updateRandomData()` - Generate random data for testing

#### C. Views
- [x] **Create Directory Structure**
  ```bash
  mkdir -p resources/views/master_items/index
  mkdir -p resources/views/master_items/form
  mkdir -p resources/views/master_items/single
  ```

- [x] **Index Views**
  - [x] `resources/views/master_items/index/index.blade.php`
    - Extends `layouts.app`
    - Include filter partial
    - Include table partial
    - Include js partial
  - [x] `resources/views/master_items/index/filter.blade.php`
    - Filter fields: kode, nama, harga min, harga max
    - Checkbox: show_deleted
    - Button: Filter (triggers AJAX)
  - [x] `resources/views/master_items/index/table.blade.php`
    - DataTable with columns: Kode, Nama, Kategori, Jenis, Harga Beli, Harga Jual, Supplier, Photo, View
  - [x] `resources/views/master_items/index/js.blade.php`
    - jQuery + DataTables CDN
    - AJAX search function
    - DataTable initialization
    - Format rupiah helper
    - Restore & force delete handlers

- [x] **Form Views**
  - [x] `resources/views/master_items/form/index.blade.php`
    - Wrapper dengan card layout
    - Dynamic title based on method
  - [x] `resources/views/master_items/form/form.blade.php`
    - Fields: photo upload, nama, harga_beli, laba, supplier (dropdown), kategoris (multi-select), jenis (dropdown)
    - CSRF token
    - Validation error display
    - Existing photo preview (for edit)

- [x] **Single View**
  - [x] `resources/views/master_items/single/index.blade.php`
    - Detail view with all item information
    - Display photo
    - Display kategoris list

#### D. Routes
- [x] **Add Routes in `routes/web.php`**
  ```php
  Route::get('/master-items', [MasterItemsController::class, 'index']);
  Route::get('/master-items/search', [MasterItemsController::class, 'search']);
  Route::get('/master-items/download-excel', [MasterItemsController::class, 'downloadExcel']);
  Route::get('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formView']);
  Route::post('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formSubmit']);
  Route::get('/master-items/view/{kode}', [MasterItemsController::class, 'singleView']);
  Route::post('/master-items/delete/{id}', [MasterItemsController::class, 'delete']);
  Route::post('/master-items/force-delete/{id}', [MasterItemsController::class, 'forceDelete']);
  Route::post('/master-items/restore/{id}', [MasterItemsController::class, 'restore']);
  Route::get('/master-items/update-random-data', [MasterItemsController::class, 'updateRandomData']);
  ```

#### E. Export Functionality
- [x] **Create Export Class**
  - File: `app/Exports/MasterItemsExport.php`
  - Command: `php artisan make:export MasterItemsExport --model=MasterItem`
  - Implements: `FromCollection`, `WithHeadings`, `WithMapping`

---

### MODULE: KATEGORI

#### A. Database & Model
- [x] **Create Migration**
  - File: `database/migrations/2026_02_21_064740_create_kategoris_table.php`
  - Command: `php artisan make:migration create_kategoris_table`
  
- [x] **Run Migration**
  ```bash
  php artisan migrate
  ```

- [x] **Create Model**
  - File: `app/Models/Kategori.php`
  - Command: `php artisan make:model Kategori`
  
- [x] **Model Configuration**
  - [x] Add `use HasFactory, SoftDeletes`
  - [x] Define `$fillable` array: `['kode', 'nama']`
  - [x] Define relationship `items()` (Many-to-Many)
  - [ ] **TODO**: Add `protected $with = ['items']` for eager loading

#### B. Controller
- [x] **Create Controller**
  - File: `app/Http/Controllers/KategoriController.php`
  - Command: `php artisan make:controller KategoriController`
  
- [x] **Implement Methods**:
  - [x] `index()` - Return view `kategoris.index.index`
  - [x] `search(Request $request)` - AJAX search & filter
    - Parameters: `kode`, `nama`
    - Returns: JSON with status 200 and data array
  - [x] `formView($method, $id)` - Show create/edit form
    - Parameters: `$method` ('new' or 'edit'), `$id` (optional)
    - Returns: View with item data
  - [x] `formSubmit(Request $request, $method, $id)` - Process form submission
    - Validation: kode (unique), nama
    - Kode is readonly on edit
  - [x] `singleView($id)` - Show detail view with items list
  - [x] `delete($id)` - Soft delete
  - [ ] **TODO**: `restore($id)` - Restore soft deleted (NOT IMPLEMENTED YET)
  - [ ] **TODO**: `forceDelete($id)` - Permanent delete (NOT IMPLEMENTED YET)
  - [x] `downloadPdf($id)` - Export to PDF using DomPDF
  - [ ] **TODO**: `downloadExcel()` - Export to Excel (NOT IMPLEMENTED YET)

#### C. Views
- [x] **Create Directory Structure**
  ```bash
  mkdir -p resources/views/kategoris/index
  mkdir -p resources/views/kategoris/form
  mkdir -p resources/views/kategoris/single
  ```

- [x] **Index Views**
  - [x] `resources/views/kategoris/index/index.blade.php`
    - Extends `layouts.app`
    - Include filter partial
    - Include table partial
    - Include js partial
  - [x] `resources/views/kategoris/index/filter.blade.php`
    - Filter fields: kode, nama
    - Button: Filter (triggers AJAX)
  - [x] `resources/views/kategoris/index/table.blade.php`
    - DataTable with columns: ID, Kode, Nama, View
  - [x] `resources/views/kategoris/index/js.blade.php`
    - jQuery + DataTables CDN
    - AJAX search function
    - DataTable initialization

- [x] **Form Views**
  - [x] `resources/views/kategoris/form/index.blade.php`
    - Wrapper dengan card layout
    - Dynamic title based on method
  - [x] `resources/views/kategoris/form/form.blade.php`
    - Fields: kode (readonly on edit), nama
    - CSRF token
    - Validation error display
    - Submit button

- [x] **Single View**
  - [x] `resources/views/kategoris/single/index.blade.php`
    - Detail view with all kategori information
    - Display items list in table
  - [x] `resources/views/kategoris/single/pdf.blade.php`
    - PDF template for export
    - Include kategori info and items table

#### D. Routes
- [x] **Add Routes in `routes/web.php`**
  ```php
  Route::get('/kategoris', [KategoriController::class, 'index']);
  Route::get('/kategoris/search', [KategoriController::class, 'search']);
  Route::get('/kategoris/form/{method}/{id?}', [KategoriController::class, 'formView']);
  Route::post('/kategoris/form/{method}/{id?}', [KategoriController::class, 'formSubmit']);
  Route::get('/kategoris/view/{id}', [KategoriController::class, 'singleView']);
  Route::get('/kategoris/download-pdf/{id}', [KategoriController::class, 'downloadPdf']);
  Route::post('/kategoris/delete/{id}', [KategoriController::class, 'delete']);
  ```

---

### MODULE: PIVOT TABLE (kategori_item)

#### A. Database
- [x] **Create Migration**
  - File: `database/migrations/2026_02_21_064748_create_kategori_item_table.php`
  - Command: `php artisan make:migration create_kategori_item_table`
  
- [x] **Migration Schema**
  ```php
  Schema::create('kategori_item', function (Blueprint $table) {
      $table->id();
      $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->onDelete('set null');
      $table->foreignId('master_item_id')->nullable()->constrained('master_items')->onDelete('set null');
      $table->timestamps();
  });
  ```

- [x] **Run Migration**
  ```bash
  php artisan migrate
  ```

---

## 🔄 Improvement Tasks (Backlog)

### High Priority
- [ ] **Add Form Request Validation Classes**
  ```bash
  php artisan make:request StoreKategoriRequest
  php artisan make:request UpdateKategoriRequest
  php artisan make:request StoreMasterItemRequest
  php artisan make:request UpdateMasterItemRequest
  ```

- [ ] **Add Default Eager Loading di Models**
  - [ ] Edit `app/Models/Kategori.php`: Add `protected $with = ['items'];`
  - [ ] Edit `app/Models/MasterItem.php`: Add `protected $with = ['kategoris'];`

- [ ] **Add Missing Kategori Features**
  - [ ] Implement `restore($id)` method in KategoriController
  - [ ] Implement `forceDelete($id)` method in KategoriController
  - [ ] Add "Show Deleted" checkbox in kategori filter
  - [ ] Add restore/force delete buttons in kategori table

- [ ] **Add Excel Export for Kategori**
  ```bash
  php artisan make:export KategoriExport --model=Kategori
  ```
  - [ ] Add `downloadExcel()` method in KategoriController
  - [ ] Add route: `GET /kategoris/download-excel`
  - [ ] Add download button in kategori index view

### Medium Priority
- [ ] **Add Photo/Icon for Kategori**
  - [ ] Create migration: `php artisan make:migration add_photo_to_kategoris_table --table=kategoris`
  - [ ] Update `Kategori` model: Add 'photo' to `$fillable`
  - [ ] Update `KategoriController@formSubmit`: Handle photo upload
  - [ ] Update `kategoris/form/form.blade.php`: Add photo upload field
  - [ ] Update `kategoris/single/index.blade.php`: Display photo

- [ ] **Add Pagination for AJAX Search**
  - [ ] Update `MasterItemsController@search`: Use `paginate(10)` instead of `get()`
  - [ ] Update `KategoriController@search`: Use `paginate(10)` instead of `get()`
  - [ ] Update JS files: Handle pagination with DataTables server-side processing

- [ ] **Add Search Scopes in Models**
  ```php
  // app/Models/Kategori.php
  public function scopeSearch($query, $kode = null, $nama = null)
  {
      if ($kode) $query->where('kode', 'LIKE', "%{$kode}%");
      if ($nama) $query->where('nama', 'LIKE', "%{$nama}%");
      return $query;
  }
  
  // Usage: Kategori::search($kode, $nama)->get();
  ```

- [ ] **Add Response Formatter**
  - [ ] Create `app/Http/Resources/KategoriResource.php`
  - [ ] Create `app/Http/Resources/MasterItemResource.php`
  - [ ] Use in controller: `return KategoriResource::collection($data);`

### Low Priority
- [ ] **Create Seeders**
  ```bash
  php artisan make:seeder KategoriSeeder
  php artisan make:seeder MasterItemSeeder
  php artisan make:factory KategoriFactory --model=Kategori
  php artisan make:factory MasterItemFactory --model=MasterItem
  ```

- [ ] **Create Unit Tests**
  ```bash
  php artisan make:test KategoriTest
  php artisan make:test MasterItemTest
  ```

- [ ] **Add API Endpoints**
  ```bash
  php artisan make:resource KategoriResource
  php artisan make:resource MasterItemResource
  ```
  - [ ] Add API routes in `routes/api.php`
  - [ ] Implement API controller methods

- [ ] **Add Activity Log**
  ```bash
  composer require spatie/laravel-activitylog
  ```
  - [ ] Add trait to models
  - [ ] Log create, update, delete actions

- [ ] **Add User Roles & Permissions**
  ```bash
  composer require spatie/laravel-permission
  ```
  - [ ] Add roles: admin, staff, dokter
  - [ ] Add middleware for protected routes

---

## 📋 Development Checklist Template (For New Modules)

### Phase 1: Planning
- [ ] Define module name (singular & plural)
- [ ] Define table schema (fields, types, constraints)
- [ ] Define relationships with other tables
- [ ] Define validation rules
- [ ] Define required features (CRUD, Export, etc.)

### Phase 2: Database
- [ ] Create migration: `php artisan make:migration create_table_name`
- [ ] Define schema in migration file
- [ ] Run migration: `php artisan migrate`
- [ ] Create foreign keys if needed

### Phase 3: Model
- [ ] Create model: `php artisan make:model ModelName`
- [ ] Add traits: `use HasFactory, SoftDeletes`
- [ ] Define `$fillable` array
- [ ] Define relationships methods
- [ ] Add `protected $with` for eager loading (optional)
- [ ] Add accessors/mutators if needed
- [ ] Add scopes if needed

### Phase 4: Controller
- [ ] Create controller: `php artisan make:controller ControllerName`
- [ ] Implement methods:
  - [ ] `index()` - Main view
  - [ ] `search(Request $request)` - AJAX search
  - [ ] `formView($method, $id)` - Form view
  - [ ] `formSubmit(Request $request, $method, $id)` - Process form
  - [ ] `singleView($id)` - Detail view
  - [ ] `delete($id)` - Soft delete
  - [ ] `restore($id)` - Restore (if soft delete)
  - [ ] `forceDelete($id)` - Permanent delete
  - [ ] `downloadExcel()` - Export Excel (if needed)
  - [ ] `downloadPdf($id)` - Export PDF (if needed)

### Phase 5: Views
- [ ] Create directory structure:
  ```bash
  mkdir -p resources/views/module_name/index
  mkdir -p resources/views/module_name/form
  mkdir -p resources/views/module_name/single
  ```
- [ ] Create index views:
  - [ ] `index/index.blade.php` - Main container
  - [ ] `index/filter.blade.php` - Filter form
  - [ ] `index/table.blade.php` - DataTable structure
  - [ ] `index/js.blade.php` - AJAX & DataTable scripts
- [ ] Create form views:
  - [ ] `form/index.blade.php` - Form wrapper
  - [ ] `form/form.blade.php` - Form content with fields
- [ ] Create single views:
  - [ ] `single/index.blade.php` - Detail view
  - [ ] `single/pdf.blade.php` - PDF template (if needed)

### Phase 6: Routes
- [ ] Add routes in `routes/web.php`
- [ ] Add menu item in `resources/views/layouts/app.blade.php`

### Phase 7: Testing
- [ ] Test create functionality
- [ ] Test read/list functionality
- [ ] Test update functionality
- [ ] Test delete functionality
- [ ] Test search/filter functionality
- [ ] Test export functionality (if any)
- [ ] Test relationship sync (if any)

### Phase 8: Documentation
- [ ] Update README.md
- [ ] Add comments in complex code
- [ ] Create API documentation (if API endpoints)

---

## 🔧 Quick Commands Reference

```bash
# === GENERATE COMMANDS ===
# Create model with migration, controller, request, factory, seeder
php artisan make:model ModelName -mcrfs

# Create only migration
php artisan make:migration create_table_name

# Create only controller
php artisan make:controller ControllerName

# Create only request
php artisan make:request StoreRequest

# Create only factory
php artisan make:factory FactoryName --model=ModelName

# Create only seeder
php artisan make:seeder SeederName

# Create only export
php artisan make:export ExportName --model=ModelName

# Create only resource (API)
php artisan make:resource ResourceName


# === MIGRATION COMMANDS ===
# Run all pending migrations
php artisan migrate

# Rollback last migration batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Fresh migration (WARNING: deletes all data)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create migration for existing table
php artisan make:migration add_column_to_table --table=table_name


# === CACHE & OPTIMIZATION ===
# Clear all cache
php artisan optimize:clear

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Create optimization cache
php artisan optimize

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache


# === OTHER USEFUL COMMANDS ===
# Create storage link (for file uploads)
php artisan storage:link

# List all routes
php artisan route:list

# Open tinker (interactive shell)
php artisan tinker

# Run seeder
php artisan db:seed --class=SeederName

# Generate IDE helper (for better autocomplete)
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
```

---

## 📊 Eager Loading Implementation Status

### Current Implementation
```php
// In MasterItemsController@search
$data_search = MasterItem::with('kategoris')
    ->select('id', 'kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'photo', 'deleted_at')
    ->orderBy('id', 'desc')
    ->get();

// In KategoriController@singleView
$data = Kategori::with('items')->findOrFail($id);
```

### Recommended Implementation (Add to Models)
```php
// app/Models/MasterItem.php
class MasterItem extends Model
{
    // ...
    protected $with = ['kategoris']; // Always load kategoris
}

// app/Models/Kategori.php
class Kategori extends Model
{
    // ...
    protected $with = ['items']; // Always load items
}
```

### Benefits of `$with` Property
| Aspect | Without `$with` | With `$with` |
|--------|----------------|--------------|
| Code Cleanliness | Must call `->with()` every time | Automatic loading |
| N+1 Prevention | Easy to forget | Always prevented |
| Performance | Depends on developer | Consistent |
| Maintenance | More code to maintain | Less code |

### Eager Loading Variations
```php
// Single relationship
$item = MasterItem::with('kategoris')->find($id);

// Multiple relationships
$item = MasterItem::with(['kategoris', 'supplier'])->get();

// Nested relationships
$item = MasterItem::with('kategoris.items')->get();

// Constrained eager loading
$item = MasterItem::with(['kategoris' => function ($query) {
    $query->where('active', true)->select('id', 'nama', 'kode');
}])->get();

// Load on existing model
$item->load('kategoris');

// Load only if not already loaded
$item->loadMissing('kategoris');
```

---

## 🎯 Priority Matrix

### P0 - Critical (Must Do)
- [ ] Add default eager loading (`$with`) to prevent N+1 queries
- [ ] Add restore functionality for Kategori (consistency with MasterItem)

### P1 - High (Should Do)
- [ ] Add Form Request validation classes
- [ ] Add Excel export for Kategori
- [ ] Add "Show Deleted" filter for Kategori

### P2 - Medium (Nice to Have)
- [ ] Add photo upload for Kategori
- [ ] Add pagination for AJAX search
- [ ] Add search scopes in models

### P3 - Low (Future Enhancement)
- [ ] Create seeders & factories
- [ ] Create unit tests
- [ ] Add API endpoints
- [ ] Add activity logging
- [ ] Add user roles & permissions

---

## 📝 Notes

1. **Kode Auto-Generate**: MasterItem menggunakan auto-generate 5 digit (00001, 00002, dst)
2. **Kode Manual**: Kategori menggunakan input manual (user menentukan sendiri)
3. **Photo Upload**: Hanya MasterItem yang support photo upload
4. **Export Format**: MasterItem → Excel, Kategori → PDF
5. **Soft Delete**: Kedua modul support soft delete, tapi Kategori belum ada restore UI
6. **Relationship Sync**: MasterItem form support multi-select kategori dengan `sync()`

---

**Last Updated**: 25 February 2026  
**Document Version**: 2.0  
**Author**: Development Team
