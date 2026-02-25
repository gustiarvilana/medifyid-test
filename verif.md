# Verifikasi Hasil Tes Coding - MedifyID Test

## 📅 Informasi Interview

| Detail | Informasi |
|--------|-----------|
| **Posisi** | Software Developer |
| **Tanggal** | Rabu, 25 Februari 2026 |
| **Waktu** | 14.00 WIB |
| **Jenis** | Interview User (Verifikasi Hasil Tes Coding) |
| **Status** | ✅ Tersedia & Bersedia |

---

## 👤 Informasi Kandidat

| Field | Detail |
|-------|--------|
| **Nama** | [Nama Kandidat] |
| **Email** | [Email Kandidat] |
| **No. HP** | [Nomor HP Kandidat] |
| **Tanggal Tes** | 24 Februari 2026 |
| **Waktu Pengerjaan** | [X] Jam |

---

## 📋 Daftar Verifikasi Hasil Coding

### 1. Pemahaman Laravel Framework

| No | Aspek | Status | Catatan |
|----|-------|--------|---------|
| 1.1 | Struktur MVC (Model-View-Controller) | ⬜ | |
| 1.2 | Migration & Schema Builder | ⬜ | |
| 1.3 | Eloquent ORM & Relationships | ⬜ | |
| 1.4 | Routing (web.php) | ⬜ | |
| 1.5 | Blade Templating | ⬜ | |
| 1.6 | Form Validation | ⬜ | |
| 1.7 | Error Handling | ⬜ | |

**Pertanyaan Verifikasi:**
- [ ] Jelaskan struktur MVC yang Anda implementasikan!
- [ ] Bagaimana cara Anda mendefinisikan relationship antar tabel?
- [ ] Apa perbedaan `->get()` dan `->paginate()`?

---

### 2. Database & Relationship

| No | Aspek | Status | Catatan |
|----|-------|--------|---------|
| 2.1 | Many-to-Many Relationship | ⬜ | kategori_item pivot table |
| 2.2 | Foreign Key Constraints | ⬜ | ON DELETE SET NULL |
| 2.3 | Soft Deletes | ⬜ | deleted_at column |
| 2.4 | Eager Loading | ⬜ | with() method |
| 2.5 | Schema Design | ⬜ | Normalisasi tabel |

**Pertanyaan Verifikasi:**
- [ ] Mengapa menggunakan Many-to-Many untuk Kategori ↔ Master Item?
- [ ] Apa perbedaan `attach()`, `detach()`, dan `sync()`?
- [ ] Kapan menggunakan Soft Delete vs Hard Delete?

**Code Review:**
```php
// Cek implementation di Model
class MasterItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [...];
    
    // Relationship Many-to-Many
    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
    }
}
```

---

### 3. CRUD Functionality

| No | Fitur | Status | File Reference |
|----|-------|--------|----------------|
| 3.1 | Create (Tambah Data) | ⬜ | `formView()`, `formSubmit()` |
| 3.2 | Read (List Data) | ⬜ | `index()`, `search()` |
| 3.3 | Update (Edit Data) | ⬜ | `formView('edit', $id)`, `formSubmit()` |
| 3.4 | Delete (Soft Delete) | ⬜ | `delete($id)` |
| 3.5 | Restore Data | ⬜ | `restore($id)` |
| 3.6 | Force Delete | ⬜ | `forceDelete($id)` |
| 3.7 | Detail View | ⬜ | `singleView($id)` |

**Pertanyaan Verifikasi:**
- [ ] Jelaskan flow dari create data!
- [ ] Bagaimana handling validasi form?
- [ ] Apa yang terjadi saat soft delete?

**Code Review Checklist:**
- [ ] Validasi input menggunakan `$request->validate()`
- [ ] Auto-generate kode barang (5 digit padding)
- [ ] Handle photo upload dengan Storage facade
- [ ] Sync relationship many-to-many

---

### 4. AJAX & Frontend Integration

| No | Fitur | Status | File Reference |
|----|-------|--------|----------------|
| 4.1 | AJAX Search | ⬜ | `search()` method |
| 4.2 | DataTables Integration | ⬜ | `js.blade.php` |
| 4.3 | Dynamic Filter | ⬜ | `filter.blade.php` |
| 4.4 | Form Submission | ⬜ | `form.blade.php` |
| 4.5 | Response JSON | ⬜ | `response()->json()` |

**Pertanyaan Verifikasi:**
- [ ] Bagaimana cara kerja AJAX search?
- [ ] Apa keuntungan menggunakan DataTables?
- [ ] Bagaimana handling error pada AJAX?

**Code Review:**
```javascript
// Cek implementation AJAX
$.ajax({
    url: '{{ url('master-items/search') }}',
    dataType: 'json',
    data: { kode: filter_kode, nama: filter_nama, ... },
    success: function(results) {
        // Handle response
    },
    error: function() {
        // Handle error
    }
});
```

---

### 5. Export Functionality

| No | Fitur | Status | File Reference |
|----|-------|--------|----------------|
| 5.1 | Export Excel (Master Item) | ⬜ | `downloadExcel()`, `MasterItemsExport` |
| 5.2 | Export PDF (Kategori) | ⬜ | `downloadPdf()`, PDF view |
| 5.3 | Package Integration | ⬜ | Maatwebsite Excel, DomPDF |

**Pertanyaan Verifikasi:**
- [ ] Package apa yang digunakan untuk export?
- [ ] Bagaimana cara membuat export class?
- [ ] Apa perbedaan export Excel vs PDF?

**Code Review:**
```php
// Excel Export
use Maatwebsite\Excel\Facades\Excel;

public function downloadExcel()
{
    return Excel::download(new MasterItemsExport, 'master-items.xlsx');
}

// PDF Export
use Barryvdh\DomPDF\Facade\Pdf;

public function downloadPdf($id)
{
    $data = Kategori::with('items')->findOrFail($id);
    $pdf = Pdf::loadView('kategoris.single.pdf', compact('data'));
    return $pdf->download('Kategori.pdf');
}
```

---

### 6. File Upload Handling

| No | Fitur | Status | File Reference |
|----|-------|--------|----------------|
| 6.1 | Photo Upload | ⬜ | `formSubmit()` |
| 6.2 | File Validation | ⬜ | `$request->validate()` |
| 6.3 | Storage Management | ⬜ | `Storage::storeAs()` |
| 6.4 | Delete File on Delete | ⬜ | `forceDelete()` |
| 6.5 | Display Uploaded File | ⬜ | `asset('storage/...')` |

**Pertanyaan Verifikasi:**
- [ ] Bagaimana validasi file upload?
- [ ] Dimana file disimpan?
- [ ] Bagaimana handling delete file?

**Code Review:**
```php
// Validation
'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'

// Upload
if ($request->hasFile('photo')) {
    $file = $request->file('photo');
    $fileName = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('public/photos', $fileName);
    $data_item->photo = $fileName;
}

// Delete on force delete
if ($item->photo) {
    Storage::delete('public/photos/' . $item->photo);
}
```

---

### 7. Code Quality & Best Practices

| No | Aspek | Status | Notes |
|----|-------|--------|-------|
| 7.1 | PSR-12 Coding Standard | ⬜ | Naming, indentation |
| 7.2 | DRY Principle | ⬜ | No code duplication |
| 7.3 | Separation of Concerns | ⬜ | Controller tidak terlalu gemuk |
| 7.4 | Error Handling | ⬜ | Try-catch, validation |
| 7.5 | Security | ⬜ | CSRF, SQL Injection prevention |
| 7.6 | Comments & Documentation | ⬜ | Code comments |

**Pertanyaan Verifikasi:**
- [ ] Apa prinsip DRY dan bagaimana implementasinya?
- [ ] Bagaimana mencegah SQL Injection di Laravel?
- [ ] Kenapa menggunakan Form Request validation?

---

### 8. Testing & Debugging

| No | Aspek | Status | Notes |
|----|-------|--------|-------|
| 8.1 | Manual Testing | ⬜ | All CRUD tested |
| 8.2 | Error Messages | ⬜ | User-friendly |
| 8.3 | Edge Cases | ⬜ | Empty data, special characters |
| 8.4 | Browser Compatibility | ⬜ | Chrome, Firefox, Edge |

**Pertanyaan Verifikasi:**
- [ ] Bagaimana cara Anda testing fitur yang dibuat?
- [ ] Apa yang dilakukan jika menemukan bug?
- [ ] Bagaimana handling invalid input?

---

## 📊 Penilaian Akhir

### Scoring Matrix

| Kategori | Bobot | Skor (1-5) | Total |
|----------|-------|------------|-------|
| Pemahaman Laravel | 15% | ⬜ | |
| Database & Relationship | 20% | ⬜ | |
| CRUD Functionality | 20% | ⬜ | |
| AJAX & Frontend | 15% | ⬜ | |
| Export & File Upload | 10% | ⬜ | |
| Code Quality | 15% | ⬜ | |
| Testing & Debugging | 5% | ⬜ | |
| **TOTAL** | **100%** | | **0** |

**Skala Penilaian:**
- 5 = Excellent (Melebihi ekspektasi)
- 4 = Good (Memenuhi ekspektasi)
- 3 = Fair (Cukup, perlu improvement)
- 2 = Poor (Kurang, perlu banyak improvement)
- 1 = Very Poor (Tidak memenuhi standar)

---

## ✅ Kesimpulan Verifikasi

### Hasil Verifikasi
- [ ] **LOLOS** - Kandidat memahami dan mampu menjelaskan kode yang dibuat
- [ ] **LOLOS DENGAN CATATAN** - Perlu improvement pada aspek tertentu
- [ ] **TIDAK LOLOS** - Kandidat tidak mampu menjelaskan kode yang dibuat

### Catatan Verifikator:

**Kekuatan Kandidat:**
```
1. 
2. 
3. 
```

**Area yang Perlu Improvement:**
```
1. 
2. 
3. 
```

### Rekomendasi:
- [ ] **Direkomendasikan untuk hire** - Kandidat kompeten
- [ ] **Dipertimbangkan** - Kandidat cukup kompeten dengan catatan
- [ ] **Tidak direkomendasikan** - Kandidat belum memenuhi standar

---

## 📝 Tanda Tangan Verifikasi

| Role | Nama | Tanggal | Tanda Tangan |
|------|------|---------|--------------|
| **Kandidat** | | 25/02/2026 | |
| **Verifikator/User** | | 25/02/2026 | |
| **HRD** | | 25/02/2026 | |

---

## 📌 Dokumen Pendukung

### File yang Perlu Ditampilkan Saat Interview:
1. [ ] `app/Models/MasterItem.php`
2. [ ] `app/Models/Kategori.php`
3. [ ] `app/Http/Controllers/MasterItemsController.php`
4. [ ] `app/Http/Controllers/KategoriController.php`
5. [ ] `database/migrations/*_create_master_items_table.php`
6. [ ] `database/migrations/*_create_kategoris_table.php`
7. [ ] `database/migrations/*_create_kategori_item_table.php`
8. [ ] `routes/web.php`
9. [ ] `resources/views/master_items/**/*.blade.php`
10. [ ] `resources/views/kategoris/**/*.blade.php`

### Live Demo yang Perlu Ditunjukkan:
1. [ ] Create Master Item dengan photo upload
2. [ ] Create Kategori
3. [ ] Assign Kategori ke Master Item (multi-select)
4. [ ] Search & Filter data
5. [ ] Export Excel (Master Item)
6. [ ] Export PDF (Kategori)
7. [ ] Soft Delete & Restore
8. [ ] Force Delete

---

**Document Created**: 25 Februari 2026  
**Version**: 1.0  
**Status**: Ready for Interview
