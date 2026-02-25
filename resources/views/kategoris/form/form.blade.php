 @if ($errors->any())
     <div class="alert alert-danger">
         <ul>
             @foreach ($errors->all() as $error)
                 <li>{{ $error }}</li>
             @endforeach
         </ul>
     </div>
 @endif
 <form method="POST" action="{{ url('kategoris/form/' . $method . ($item->id ? '/' . $item->id : '')) }}">
     @csrf

     <div class="form-group mb-3">
         <label for="kode">Kode Kategori <span class="text-danger">*</span></label>
         <input type="text" class="form-control @error('kode') is-invalid @enderror" id="kode" name="kode"
             value="{{ old('kode', $item->kode ?? '') }}" required {{ $method == 'edit' ? 'readonly' : '' }}>
         @error('kode')
             <div class="invalid-feedback">{{ $message }}</div>
         @enderror
     </div>

     <div class="form-group mb-3">
         <label for="nama">Nama Kategori <span class="text-danger">*</span></label>
         <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
             value="{{ old('nama', $item->nama ?? '') }}" required>
         @error('nama')
             <div class="invalid-feedback">{{ $message }}</div>
         @enderror
     </div>

     <div class="form-group mb-3">
         <button type="submit" class="btn btn-primary">Simpan</button>
         <a href="{{ url('kategoris') }}" class="btn btn-secondary">Batal</a>
     </div>
 </form>
