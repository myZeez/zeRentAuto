<label>Warna unit<input name="color" required maxlength="40" value="{{ old('color', $unit?->color) }}" placeholder="Contoh: Silver"></label>
<label>Warna penanda<input type="color" name="color_hex" required value="{{ old('color_hex', $unit?->color_hex ?? '#bfc4c9') }}"></label>
<label>Kode unit<input name="code" maxlength="30" value="{{ old('code', $unit?->code) }}" placeholder="Otomatis jika dikosongkan"><small>Kode unik untuk membedakan kendaraan.</small></label>
<label>Nomor polisi<input name="registration" maxlength="20" value="{{ old('registration', $unit?->registration) }}" placeholder="Contoh: B 1234 ZE"><small>Hanya ditampilkan kepada admin.</small></label>
<label>Status unit<select name="status">@foreach(\App\Models\CarUnit::STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('status', $unit?->status ?? 'ready') === $value)>{{ $label }}</option>@endforeach</select><small>Status sedang disewa dihitung otomatis dari reservasi.</small></label>
<label>Foto unit<input type="file" name="image_upload" accept="image/jpeg,image/png,image/webp"><small>Opsional · JPG, PNG, atau WebP, maksimal 4 MB. Jika kosong, memakai foto tipe mobil.</small></label>
<label>URL foto<input name="image" value="{{ old('image', $unit?->image) }}" placeholder="https://… atau /storage/…"></label>
