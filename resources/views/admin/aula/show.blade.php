@php
    $user = Auth::user();
    $canManageAula = $user && ($user->isAdmin() || $user->role === 'pengelola_aula');
    $isAdmin = $user?->isAdmin() ?? false;
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.user';
    $photos = is_array($aula->foto) ? $aula->foto : [];
    $facilities = is_array($aula->fasilitas) ? $aula->fasilitas : [];
@endphp
@extends($layout)
@section('title', 'Detail Aula')
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('admin.aula.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">← Kembali ke daftar aula</a>
            <p class="mt-5 text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">Detail fasilitas</p>
            <h1 class="mt-2 text-3xl md:text-4xl font-bold text-gray-900">{{ $aula->nama }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ $aula->status_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $aula->status_aktif ? '● Aktif' : '● Tidak aktif' }}</span>
            @if($canManageAula)<a href="{{ route('admin.aula.edit', $aula->id) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Edit aula</a>@endif
        </div>
    </div>
    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-5">
            <div class="lg:col-span-3 bg-gradient-to-br from-indigo-50 via-white to-slate-100 p-5 md:p-8">
                @if(count($photos) > 0)
                    <div class="grid grid-cols-2 gap-3">@foreach($photos as $index => $photo)<div class="overflow-hidden rounded-2xl {{ $index === 0 ? 'col-span-2 h-72 md:h-96' : 'h-32 md:h-44' }} bg-gray-100"><img src="{{ asset('storage/' . $photo) }}" alt="{{ $aula->nama }} - foto {{ $index + 1 }}" class="h-full w-full object-cover transition duration-500 hover:scale-105"></div>@endforeach</div>
                @else
                    <div class="flex h-80 items-center justify-center rounded-2xl border-2 border-dashed border-indigo-200 bg-indigo-50/60 text-center"><div><div class="text-5xl">🏛️</div><p class="mt-3 font-semibold text-gray-600">Belum ada foto aula</p></div></div>
                @endif
            </div>
            <div class="lg:col-span-2 p-6 md:p-8">
                <div class="grid grid-cols-2 gap-4 pb-7 border-b border-gray-100"><div class="rounded-2xl bg-indigo-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Kapasitas</p><p class="mt-2 text-2xl font-bold text-indigo-900">{{ number_format($aula->kapasitas) }}</p><p class="text-sm text-indigo-700">orang</p></div><div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</p><p class="mt-2 font-bold text-gray-900">{{ $aula->lokasi ?: '-' }}</p></div></div>
                <div class="mt-7 space-y-6"><div><h2 class="text-lg font-bold text-gray-900">Deskripsi</h2><p class="mt-2 leading-relaxed text-gray-600">{{ $aula->deskripsi ?: 'Belum ada deskripsi.' }}</p></div><div><h2 class="text-lg font-bold text-gray-900">Fasilitas</h2>@if(count($facilities) > 0)<div class="mt-3 flex flex-wrap gap-2">@foreach($facilities as $facility)<span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">{{ $facility }}</span>@endforeach</div>@else<p class="mt-2 text-gray-500">Belum ada fasilitas yang dicatat.</p>@endif</div>@if($aula->informasi_tambahan)<div class="rounded-2xl bg-amber-50 p-4 text-amber-900"><h2 class="font-bold">Informasi tambahan</h2><p class="mt-2 whitespace-pre-line text-sm leading-relaxed">{{ $aula->informasi_tambahan }}</p></div>@endif</div>
            </div>
        </div>
    </div>
</div>
@endsection
