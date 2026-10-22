@php
    $cardStyles = [
        'red' => [
            'classes' => 'bg-red-600 text-white border-red-700 dark:bg-red-950/60 dark:text-red-300 dark:border-red-900/60 font-semibold',
        ],
        'yellow' => [
            'classes' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/60 font-semibold',
        ],
        'blue' => [
            'classes' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900/60 font-semibold',
        ],
        'green' => [
            'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/60 font-semibold',
        ],
        'gray' => [
            'classes' => 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-neutral-900 dark:text-neutral-300 dark:border-neutral-800 font-semibold',
        ],
    ];

    $style = $cardStyles[$pajak->warna] ?? $cardStyles['gray'];
@endphp

<div class="border rounded-lg p-2.5 text-center transition-colors {{ $style['classes'] }}">
    <p class="text-xs font-semibold">{{ $pajak->label_sisa }}</p>
</div>