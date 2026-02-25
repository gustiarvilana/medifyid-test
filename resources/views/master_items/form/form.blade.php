 @if ($errors->any())
     <div class="alert alert-danger">
         <ul>
             @foreach ($errors->all() as $error)
                 <li>{{ $error }}</li>
             @endforeach
         </ul>
     </div>
 @endif
 <form method="POST" enctype="multipart/form-data">
     @csrf
     @if ($method == 'edit')
         <div class="form-group">
             <label>Kode Barang</label>
             <input type="text" class="form-control" name="kode_barang" required readonly
                 value="{{ $item->kode ?? '' }}">
         </div>
     @endif

     <div class="form-group mb-3">
         <label for="photo">Photo Item</label>
         <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo"
             accept="image/*">
         <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB</small>
         @error('photo')
             <div class="invalid-feedback">{{ $message }}</div>
         @enderror

         @if (isset($item->photo) && $item->photo)
             <div class="mt-2">
                 <label>Photo Saat Ini:</label>
                 <div>
                     <img src="{{ asset('storage/photos/' . $item->photo) }}" width="150" height="150"
                         style="object-fit: cover; border-radius: 5px;">
                 </div>
             </div>
         @endif
     </div>

     <div class="form-group">
         <label>Nama</label>
         <input type="text" class="form-control" name="nama" required value="{{ $item->nama ?? '' }}">
     </div>

     <div class="form-group">
         <label>Harga Beli</label>
         <input type="number" class="form-control" name="harga_beli" required value="{{ $item->harga_beli ?? '' }}">
     </div>

     <div class="form-group">
         <label>Laba (dalam persen)</label>
         <input type="number" class="form-control" name="laba" required value="{{ $item->laba ?? '' }}">
     </div>

     @php $selected = $item->supplier ?? ''; @endphp
     <div class="form-group">
         <label>Supplier</label>
         <select class="form-control" required name="supplier">
             <option @if ($selected == '') selected @endif value="">--Pilih--</option>
             <option @if ($selected == 'Tokopaedi') selected @endif>Tokopaedi</option>
             <option @if ($selected == 'Bukulapuk') selected @endif>Bukulapuk</option>
             <option @if ($selected == 'TokoBagas') selected @endif>TokoBagas</option>
             <option @if ($selected == 'E Commurz') selected @endif>E Commurz</option>
             <option @if ($selected == 'Blublu') selected @endif>Blublu</option>
         </select>
     </div>

     <div class="form-group mb-3">
         <label for="kategoris">Kategori</label>
         <select class="form-control" name="kategoris[]" id="kategoris" multiple>
             @foreach ($kategoris as $kat)
                 @php
                     $selected = false;
                     if (isset($item) && $item->kategoris) {
                         $selected = $item->kategoris->contains($kat->id);
                     }
                 @endphp
                 <option value="{{ $kat->id }}" {{ $selected ? 'selected' : '' }}>{{ $kat->nama }}</option>
             @endforeach
         </select>
         <small class="text-muted">Tahan Ctrl untuk memilih lebih dari satu</small>
     </div>

     @php $selected = $item->jenis ?? ''; @endphp
     <div class="form-group">
         <label>Jenis</label>
         <select class="form-control" required name="jenis">
             <option @if ($selected == '') selected @endif value="">--Pilih--</option>
             <option @if ($selected == 'Obat') selected @endif>Obat</option>
             <option @if ($selected == 'Alkes') selected @endif>Alkes</option>
             <option @if ($selected == 'Matkes') selected @endif>Matkes</option>
             <option @if ($selected == 'Umum') selected @endif>Umum</option>
             <option @if ($selected == 'ATK') selected @endif>ATK</option>
         </select>
     </div>

     <button class="btn btn-primary mt-3">Submit</button>

 </form>
