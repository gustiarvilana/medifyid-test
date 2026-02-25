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
        <div class="form-group mb-3">
            <label>Kode Pasien</label>
            <input type="text" class="form-control" name="kode_pasien" required readonly
                value="{{ $item->kode ?? '' }}">
            <small class="text-muted">Kode tidak dapat diubah</small>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="photo">Photo Pasien</label>
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
                            <img src="{{ asset('storage/photos/pasien/' . $item->photo) }}" width="150" height="150"
                                style="object-fit: cover; border-radius: 5px;">
                        </div>
                    </div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
                    value="{{ old('nama', $item->nama ?? '') }}" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="nik">NIK</label>
                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik"
                    value="{{ old('nik', $item->nik ?? '') }}" maxlength="16" pattern="[0-9]{16}">
                <small class="text-muted">16 digit Nomor Induk Kependudukan</small>
                @error('nik')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="tempat_lahir">Tempat Lahir</label>
                <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" name="tempat_lahir"
                    value="{{ old('tempat_lahir', $item->tempat_lahir ?? '') }}">
                @error('tempat_lahir')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir"
                    value="{{ old('tanggal_lahir', $item->tanggal_lahir ?? '') }}">
                @error('tanggal_lahir')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ (old('jenis_kelamin', $item->jenis_kelamin ?? '') == 'L') ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ (old('jenis_kelamin', $item->jenis_kelamin ?? '') == 'P') ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="golongan_darah">Golongan Darah</label>
                <select class="form-control @error('golongan_darah') is-invalid @enderror" id="golongan_darah" name="golongan_darah">
                    <option value="">-- Pilih --</option>
                    <option value="A" {{ (old('golongan_darah', $item->golongan_darah ?? '') == 'A') ? 'selected' : '' }}>A</option>
                    <option value="B" {{ (old('golongan_darah', $item->golongan_darah ?? '') == 'B') ? 'selected' : '' }}>B</option>
                    <option value="AB" {{ (old('golongan_darah', $item->golongan_darah ?? '') == 'AB') ? 'selected' : '' }}>AB</option>
                    <option value="O" {{ (old('golongan_darah', $item->golongan_darah ?? '') == 'O') ? 'selected' : '' }}>O</option>
                </select>
                @error('golongan_darah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="agama">Agama</label>
                <select class="form-control @error('agama') is-invalid @enderror" id="agama" name="agama">
                    <option value="">-- Pilih --</option>
                    <option value="Islam" {{ (old('agama', $item->agama ?? '') == 'Islam') ? 'selected' : '' }}>Islam</option>
                    <option value="Kristen" {{ (old('agama', $item->agama ?? '') == 'Kristen') ? 'selected' : '' }}>Kristen</option>
                    <option value="Katolik" {{ (old('agama', $item->agama ?? '') == 'Katolik') ? 'selected' : '' }}>Katolik</option>
                    <option value="Hindu" {{ (old('agama', $item->agama ?? '') == 'Hindu') ? 'selected' : '' }}>Hindu</option>
                    <option value="Buddha" {{ (old('agama', $item->agama ?? '') == 'Buddha') ? 'selected' : '' }}>Buddha</option>
                    <option value="Konghucu" {{ (old('agama', $item->agama ?? '') == 'Konghucu') ? 'selected' : '' }}>Konghucu</option>
                </select>
                @error('agama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="pekerjaan">Pekerjaan</label>
                <input type="text" class="form-control @error('pekerjaan') is-invalid @enderror" id="pekerjaan" name="pekerjaan"
                    value="{{ old('pekerjaan', $item->pekerjaan ?? '') }}">
                @error('pekerjaan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="no_telepon">No. Telepon</label>
                <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon"
                    value="{{ old('no_telepon', $item->no_telepon ?? '') }}">
                @error('no_telepon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email', $item->email ?? '') }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="alamat">Alamat</label>
                <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $item->alamat ?? '') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ $method == 'new' ? 'Tambah' : 'Update' }} Pasien
        </button>
        <a href="{{ url('pasiens') }}" class="btn btn-secondary">Batal</a>
    </div>

</form>
