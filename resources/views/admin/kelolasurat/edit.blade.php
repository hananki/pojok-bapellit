@php
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $currentRole = $user?->role ?? 'user';
    
    // Tentukan layout berdasarkan role (Admin & Pengelola Surat pakai layout Admin)
    $isPanelUser = in_array($currentRole, ['admin', 'pengelola_surat']);
    $layout = $isPanelUser ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Edit Surat - ' . $surat->no_surat)

@section('content')
<div class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-12 py-8">

    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Surat</h1>
            <p class="text-base text-gray-600 mt-1">Perbarui data surat nomor <span class="font-bold text-indigo-600">{{ $surat->no_surat }}</span></p>
        </div>
        <a href="{{ route('admin.kelolasurat.index') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-base font-medium shadow-sm whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.kelolasurat.update', $surat->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8 space-y-6">
                
                <!-- Baris 1: No Surat & Tanggal (READONLY) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nomor Surat <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-gray-400 ml-1">(Tidak dapat diubah)</span>
                        </label>
                        <input type="text" name="no_surat" value="{{ old('no_surat', $surat->no_surat) }}" required readonly
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 font-mono text-sm cursor-not-allowed focus:outline-none"
                            placeholder="Contoh: 001/BAPPELIT/IX/2024">
                        @error('no_surat') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Surat
                            <span class="text-xs font-normal text-gray-400 ml-1">(Tidak dapat diubah)</span>
                        </label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $surat->tanggal?->format('Y-m-d')) }}" readonly
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none">
                        @error('tanggal') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Baris 2: Jenis & Sifat -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Surat</label>
                        <input type="text" name="jenis_surat" value="{{ old('jenis_surat', $surat->jenis_surat) }}"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('jenis_surat') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="Surat Masuk / Keluar / Undangan">
                        @error('jenis_surat') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sifat Surat</label>
                        <input type="text" name="sifat_surat" value="{{ old('sifat_surat', $surat->sifat_surat) }}"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('sifat_surat') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="Penting / Biasa / Rahasia">
                        @error('sifat_surat') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Baris 3: Asal & Tujuan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Asal Surat / Pengirim</label>
                        <input type="text" name="asal_surat" value="{{ old('asal_surat', $surat->asal_surat) }}"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('asal_surat') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="Instansi pengirim">
                        @error('asal_surat') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tujuan Surat / Penerima</label>
                        <input type="text" name="alamat_tujuan" value="{{ old('alamat_tujuan', $surat->alamat_tujuan) }}"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('alamat_tujuan') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="Instansi penerima">
                        @error('alamat_tujuan') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Baris 4: No Indek & Lampiran -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">No / Indek</label>
                        <input type="text" name="no_indek" value="{{ old('no_indek', $surat->no_indek) }}"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('no_indek') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="Nomor indeks arsip">
                        @error('no_indek') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Banyak Lampiran</label>
                        <input type="number" name="banyak_lampiran" value="{{ old('banyak_lampiran', $surat->banyak_lampiran) }}" min="0"
                            class="w-full px-4 py-3 rounded-lg border {{ $errors->has('banyak_lampiran') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white"
                            placeholder="0">
                        @error('banyak_lampiran') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Isi Surat -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Isi Surat <span class="text-red-500">*</span></label>
                    <textarea name="isi_surat" rows="6" required
                        class="w-full px-4 py-3 rounded-lg border {{ $errors->has('isi_surat') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white leading-relaxed"
                        placeholder="Tuliskan isi atau ringkasan surat...">{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                    @error('isi_surat') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full px-4 py-3 rounded-lg border {{ $errors->has('keterangan') ? 'border-red-300' : 'border-gray-300' }} focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition bg-white leading-relaxed"
                        placeholder="Catatan internal atau keterangan lain...">{{ old('keterangan', $surat->keterangan) }}</textarea>
                    @error('keterangan') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <!-- Upload File Section -->
                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">File Lampiran Digital</label>
                    
                    <!-- Info File Lama -->
                    @if($surat->file_surat)
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-blue-900 truncate">{{ $surat->file_surat_original_name ?? 'File Terlampir' }}</p>
                                    <p class="text-xs text-blue-600">File saat ini tersimpan di sistem</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.kelolasurat.download-file', $surat->id) }}" target="_blank"
                               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Lihat File
                            </a>
                        </div>
                    @endif

                    <!-- Dropzone Upload Baru -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-500 hover:bg-indigo-50/30 transition bg-gray-50 relative cursor-pointer group" id="dropzone">
                        <input type="file" name="file_surat" accept=".pdf,.doc,.docx,.xls,.xlsx" 
                            class="absolute inset-0 opacity-0 cursor-pointer z-10" 
                            id="fileInput"
                            onchange="updateFileName(this)">
                        
                        <div id="uploadPreview" class="pointer-events-none">
                            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700" id="fileNameDisplay">
                                Klik untuk upload file baru atau drag file ke sini
                            </p>
                            <p class="text-xs text-gray-400 mt-1">PDF, DOC, DOCX, XLS, XLSX (Max 5MB)</p>
                            <p class="text-xs text-indigo-500 mt-2 font-medium">* Kosongkan jika tidak ingin mengubah file</p>
                        </div>
                    </div>
                    @error('file_surat') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="px-6 md:px-8 py-5 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.kelolasurat.index') }}" 
                   class="px-6 py-2.5 text-base font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition">
                    Batal
                </a>
                <button type="submit" 
                    class="inline-flex items-center gap-2 px-7 py-2.5 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        const preview = document.getElementById('uploadPreview');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const size = (file.size / 1024 / 1024).toFixed(2);
            
            display.innerHTML = `
                <span class="text-indigo-600 font-bold flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    ${file.name}
                </span>
                <span class="block text-xs text-gray-500 mt-1">${size} MB - Siap diupload</span>
            `;
            preview.classList.add('bg-white', 'shadow-sm', 'rounded-lg', 'p-4');
        }
    }

    // Drag & drop visual effect
    const dropzone = document.getElementById('dropzone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            if (!document.getElementById('fileInput').files.length) {
                dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
            }
        });
    });
</script>
@endsection