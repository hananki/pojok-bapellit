@php
    use App\Models\Kendaraan;
    
    $expiringKendaraan = collect();
    if (auth()->check()) {
        $query = Kendaraan::with('pajakKendaraan');
        $allKendaraan = $query->get();
        foreach ($allKendaraan as $k) {
            // Ambil pajak yang belum bayar dan paling dekat jatuh tempo
            $pajakAktif = $k->pajakKendaraan
                ->where('status', 'belum_bayar')
                ->sortBy('tanggal_jatuh_tempo')
                ->first();
            
            if ($pajakAktif && $pajakAktif->sisa_hari !== null && $pajakAktif->sisa_hari <= 60) {
                $expiringKendaraan->push([
                    'kendaraan' => $k,
                    'pajak' => $pajakAktif
                ]);
            }
        }
    }
@endphp

@if($expiringKendaraan->count() > 0 && !session()->has('tax_alert_dismissed'))
<div x-data="{ show: true }" x-show="show" class="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500 p-4 mb-6 shadow-sm relative rounded-r-lg dark:border-amber-500" role="alert">
    <div class="flex items-start justify-between">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 text-amber-500 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Peringatan Masa Berlaku Pajak Kendaraan</h3>
                <div class="mt-1 text-sm text-amber-700 dark:text-amber-300 space-y-1">
                    <p>Terdapat <strong>{{ $expiringKendaraan->count() }}</strong> kendaraan yang masa berlaku pajaknya sudah dekat atau habis:</p>
                    <ul class="list-disc list-inside text-xs space-y-0.5 pl-1">
                        @foreach($expiringKendaraan as $item)
                            <li>
                                <span class="font-semibold text-amber-900 dark:text-amber-100">{{ $item['kendaraan']->merk ?? 'Kendaraan' }}</span> (Plat: {{ $item['kendaraan']->plat_nomor }}) - 
                                @if($item['pajak']->sisa_hari < 0)
                                    <span class="text-red-600 dark:text-red-400 font-bold">Terlambat {{ abs($item['pajak']->sisa_hari) }} hari</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-300 font-semibold">Jatuh tempo dalam {{ $item['pajak']->sisa_hari }} hari ({{ \Carbon\Carbon::parse($item['pajak']->tanggal_jatuh_tempo)->format('d/m/Y') }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <button @click="show = false; fetch('{{ route('kendaraan.dismiss-alert') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}})" class="text-amber-400 hover:text-amber-600 dark:hover:text-amber-300 focus:outline-none p-1 rounded-full hover:bg-amber-100 dark:hover:bg-amber-900/30 transition">
            <span class="sr-only">Tutup</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif
