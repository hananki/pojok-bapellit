<div class="space-y-8">
    <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 md:p-6">
        <div class="mb-5"><h2 class="text-lg font-bold text-slate-900">Informasi kendaraan</h2><p class="mt-1 text-sm text-slate-500">Isi identitas kendaraan dan status penggunaannya.</p></div>
        <div class="grid gap-5 md:grid-cols-2">
            @foreach([['plat_nomor', 'Plat Nomor', 'text', true], ['merk', 'Merk Kendaraan', 'text', false], ['nomor_rangka', 'Nomor Rangka', 'text', false], ['unit_kerja', 'Unit Kerja', 'text', false]] as [$name, $label, $type, $required])
                <div><label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label><input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $kendaraan?->$name) }}" @required($required) class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500"><x-input-error :messages="$errors->get($name)" class="mt-1" /></div>
            @endforeach
            
            <!-- Pemakai dari User -->
            <div x-data="{ 
                    useUser: {{ old('user_id', $kendaraan?->user_id) ? 'true' : 'false' }},
                    updatePemakaiHidden(event) {
                        const select = event.target;
                        const selectedOption = select.options[select.selectedIndex];
                        const userName = selectedOption.getAttribute('data-name');
                        const hiddenInput = document.getElementById('pemakai_manual');
                        if (hiddenInput && userName) {
                            hiddenInput.value = userName; // Pindahkan nama user ke hidden input
                        }
                    }
                }">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Pemakai</label>
                <div class="mb-2 flex gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="pemakai_type" value="user" @click="useUser = true" :checked="useUser" class="text-indigo-600">
                        <span class="ml-2 text-sm">Pilih dari User</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="pemakai_type" value="manual" @click="useUser = false; document.getElementById('pemakai_manual').value = ''; document.getElementById('user_id').value = '';" :checked="!useUser" class="text-indigo-600">
                        <span class="ml-2 text-sm">Isi Manual</span>
                    </label>
                </div>
                
                <!-- Dropdown User -->
                <div x-show="useUser" x-cloak>
                    <select id="user_id" name="user_id" @change="updatePemakaiHidden($event)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-name="{{ $user->name }}" @selected(old('user_id', $kendaraan?->user_id) == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                </div>
                
                <!-- Input Manual -->
                <div x-show="!useUser" x-cloak>
                    <input id="pemakai_manual" type="text" name="pemakai" value="{{ old('pemakai', $kendaraan?->pemakai) }}" placeholder="Nama pemakai kendaraan" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500">
                    <x-input-error :messages="$errors->get('pemakai')" class="mt-1" />
                </div>
            </div>
            
            <div><label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label><select id="status" name="status" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">@foreach(['aktif', 'tidak dipakai', 'rusak'] as $status)<option value="{{ $status }}" @selected(old('status', $kendaraan?->status ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 p-5 md:p-6" x-data="taxDateHelpers()">
        <div class="mb-5"><h2 class="text-lg font-bold text-slate-900">Masa berlaku pajak</h2><p class="mt-1 text-sm text-slate-500">Pilih tanggal dari kalender atau gunakan tombol cepat.</p></div>
        <div class="grid gap-5 md:grid-cols-2">
            @foreach([['tahunan', 'Tanggal Jatuh Tempo Tahunan', 1], ['5_tahunan', 'Tanggal Jatuh Tempo 5 Tahunan', 5]] as [$jenis, $label, $years])
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><label for="tanggal_jatuh_tempo_{{ $jenis }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ $label }} <span class="text-red-500">*</span></label><input id="tanggal_jatuh_tempo_{{ $jenis }}" type="date" name="tanggal_jatuh_tempo_{{ $jenis }}" value="{{ old('tanggal_jatuh_tempo_'.$jenis, $kendaraan?->{'pajak'.($jenis === 'tahunan' ? 'TahunanAktif' : 'LimaTahunanAktif')}?->tanggal_jatuh_tempo?->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><button type="button" @click="setFromToday('tanggal_jatuh_tempo_{{ $jenis }}', {{ $years }})" class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800">Gunakan tanggal {{ $years }} tahun dari hari ini</button><x-input-error :messages="$errors->get('tanggal_jatuh_tempo_'.$jenis)" class="mt-1" /></div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 p-5 md:p-6" x-data="vehiclePhotoPreview()">
        <div class="mb-5"><h2 class="text-lg font-bold text-slate-900">Foto kendaraan</h2><p class="mt-1 text-sm text-slate-500">Gunakan foto yang jelas. Format gambar apa saja, maksimal 15 MB.</p></div>
        <label for="foto" class="mb-2 block text-sm font-semibold text-slate-700">Pilih foto</label><input id="foto" type="file" name="foto" accept="image/*" @change="preview($event)" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"><x-input-error :messages="$errors->get('foto')" class="mt-1" />
        <div class="mt-4 overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50"><template x-if="imageUrl"><div class="relative"><img :src="imageUrl" alt="Preview foto kendaraan" class="h-52 w-full object-cover"><button type="button" @click="clear()" class="absolute right-3 top-3 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white shadow hover:bg-red-700">Hapus preview</button><p class="truncate bg-white px-3 py-2 text-xs text-slate-500" x-text="fileName"></p></div></template><template x-if="!imageUrl"><div class="flex min-h-36 items-center justify-center px-4 text-center text-sm text-slate-400">@if($kendaraan?->foto_url)<div><img src="{{ $kendaraan->foto_url }}" alt="Foto kendaraan saat ini" class="mx-auto mb-2 h-28 rounded-lg object-cover"><span>Foto saat ini — pilih file baru untuk mengganti</span></div>@else Pilih foto untuk melihat preview di sini @endif</div></template></div>
    </section>

    <section class="rounded-2xl border border-slate-200 p-5 md:p-6"><label for="catatan" class="block text-lg font-bold text-slate-900">Catatan <span class="text-sm font-normal text-slate-400">(opsional)</span></label><p class="mt-1 text-sm text-slate-500">Tambahkan informasi tambahan jika diperlukan.</p><textarea id="catatan" name="catatan" rows="4" class="mt-4 w-full rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: kondisi kendaraan, keterangan dokumen, atau catatan lainnya...">{{ old('catatan', $kendaraan?->catatan) }}</textarea></section>
</div>

@once
    @push('scripts')
        <script>
            function taxDateHelpers() {
                return {
                    setFromToday(inputId, years) {
                        const date = new Date();
                        date.setFullYear(date.getFullYear() + years);
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        document.getElementById(inputId).value = `${date.getFullYear()}-${month}-${day}`;
                    },
                };
            }

            function vehiclePhotoPreview() {
                return { imageUrl: null, fileName: '', preview(event) { const file = event.target.files[0]; if (!file) return; if (this.imageUrl) URL.revokeObjectURL(this.imageUrl); this.imageUrl = URL.createObjectURL(file); this.fileName = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`; }, clear() { const input = document.getElementById('foto'); input.value = ''; if (this.imageUrl) URL.revokeObjectURL(this.imageUrl); this.imageUrl = null; this.fileName = ''; } };
            }
        </script>
        
        <style>
            [x-cloak] { display: none !important; }
        </style>
    @endpush
@endonce