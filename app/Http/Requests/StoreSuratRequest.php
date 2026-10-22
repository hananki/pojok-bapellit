<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_surat' => 'nullable|string|max:100',
            'sifat_surat' => 'nullable|string|max:100',
            'tanggal' => 'nullable|date',
            'no_indek' => 'nullable|string|max:255',
            'alamat_tujuan' => 'nullable|string|max:255',
            'isi_surat' => 'nullable|string', // Diubah menjadi nullable agar tidak wajib diisi
            'keterangan' => 'nullable|string',
            'file_surat' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Diubah menjadi nullable
            'no_surat_hidden' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // Pesan error untuk isi_surat dan file_surat dihapus karena sekarang opsional
            'file_surat.mimes' => 'File harus berupa PDF, DOC, DOCX, XLS, atau XLSX',
            'file_surat.max' => 'Ukuran file maksimal 5MB',
        ];
    }
}