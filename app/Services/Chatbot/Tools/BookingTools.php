<?php

namespace App\Services\Chatbot\Tools;

use App\Http\Controllers\BookingController;
use App\Models\Aula;
use App\Models\Booking;
use App\Services\Chatbot\Support\DateParser;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Tool AI untuk BOOKING AULA:
 *  - check_booking_availability : cek satu aula pada tanggal + sesi
 *  - list_available_aulas       : daftar semua aula yang kosong pada tanggal tertentu
 *  - create_booking             : buat booking baru (status Pending)
 *  - get_riwayat_booking        : 5 riwayat booking terakhir user
 */
class BookingTools implements ToolGroup
{
    private const TOOLS = [
        'check_booking_availability',
        'list_available_aulas',
        'create_booking',
        'get_riwayat_booking',
    ];

    public function __construct(
        private DateParser $dates,
        private ToolSupport $support,
    ) {
    }

    public function handles(string $name): bool
    {
        return in_array($name, self::TOOLS, true);
    }

    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_booking_availability',
                    'description' => 'Cek apakah aula tersedia pada tanggal dan sesi tertentu',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'aula_id' => ['type' => 'integer', 'description' => 'ID Aula (lihat DATA AULA di knowledge base)'],
                            'tanggal_booking' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'sesi_waktu' => ['type' => 'string', 'enum' => ['pagi', 'siang', 'seharian']],
                        ],
                        'required' => ['aula_id', 'tanggal_booking', 'sesi_waktu'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_booking',
                    'description' => 'Buat booking aula baru. HANYA panggil setelah user mengonfirmasi ketersediaan.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'aula_id' => ['type' => 'integer'],
                            'tanggal_booking' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'nama_penanggung_jawab' => ['type' => 'string'],
                            'keperluan' => ['type' => 'string'],
                            'jumlah_peserta' => ['type' => 'integer'],
                            'sesi_waktu' => ['type' => 'string', 'enum' => ['pagi', 'siang', 'seharian']],
                            'catatan' => ['type' => 'string'],
                        ],
                        'required' => ['aula_id', 'tanggal_booking', 'nama_penanggung_jawab', 'keperluan', 'jumlah_peserta', 'sesi_waktu'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_available_aulas',
                    'description' => 'Lihat SEMUA aula yang kosong/tersedia pada tanggal tertentu (atau besok). Pakai ini jika user bertanya aula apa saja yang kosong, bukan untuk satu aula spesifik.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tanggal_booking' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD (ubah dari nama bulan Indonesia/Inggris atau kata seperti besok)'],
                            'sesi_waktu' => ['type' => 'string', 'enum' => ['pagi', 'siang', 'seharian'], 'description' => 'Opsional. Kosongkan jika user tidak menyebut sesi.'],
                        ],
                        'required' => ['tanggal_booking'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_riwayat_booking',
                    'description' => 'Lihat 5 riwayat booking terakhir milik user.',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
        ];
    }

    public function run(string $name, array &$arguments, Authenticatable $user): string
    {
        return match ($name) {
            'check_booking_availability' => $this->checkAvailability($arguments, $user),
            'list_available_aulas' => $this->listAvailable($arguments, $user),
            'create_booking' => $this->createBooking($arguments, $user),
            'get_riwayat_booking' => $this->history($user),
            default => "Fungsi tidak dikenal: {$name}",
        };
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        if ($name === 'check_booking_availability' && ($result === 'Tersedia' || str_starts_with($result, 'Sudah dibooking'))) {
            $aulaId = $arguments['aula_id'] ?? null;
            $aula = is_numeric($aulaId) ? Aula::find((int) $aulaId) : null;
            $nama = $aula?->nama ?? ('ID '.($aulaId ?? '?'));

            $date = $this->dates->parse((string) ($arguments['tanggal_booking'] ?? ''));
            $tanggal = $date ? $this->dates->formatIndo($date) : ($arguments['tanggal_booking'] ?? '-');

            return "Aula {$nama} pada {$tanggal} sesi ".($arguments['sesi_waktu'] ?? '-').": {$result}";
        }

        return $result;
    }

    // ── Implementasi tiap tool ───────────────────────────────────────

    private function checkAvailability(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $dateError;
        }

        $request = $this->support->makeRequest($arguments, $user);
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
            'tanggal_booking' => 'required|date',
            'sesi_waktu' => 'required|in:pagi,siang,seharian',
        ]);

        [$controller, $method] = $this->availabilityMethod();

        return $method->invoke($controller, $request) ? 'Tersedia' : 'Sudah dibooking pada waktu tersebut.';
    }

    private function listAvailable(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $dateError;
        }

        return $this->listAvailableAulas($user, $arguments['tanggal_booking'], $arguments['sesi_waktu'] ?? null);
    }

    private function createBooking(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $dateError;
        }

        $request = $this->support->makeRequest($arguments, $user);
        $request->merge(['user_id' => $user->getAuthIdentifier()]);

        $response = (new BookingController())->store($request);

        return $this->support->summarize($response, 'Booking berhasil dibuat dengan status Pending.');
    }

    private function history(Authenticatable $user): string
    {
        $bookings = Booking::where('user_id', $user->getAuthIdentifier())
            ->with('aula')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $bookings->isEmpty()
            ? 'Belum ada riwayat booking.'
            : $bookings->map(fn ($b) => '- '.($b->aula?->nama ?? 'Aula tidak ditemukan')." pada {$b->tanggal_booking} ({$b->status})")->implode("\n");
    }

    /**
     * isAvailable() milik BookingController dipanggil lewat reflection (method-nya private/protected).
     */
    private function availabilityMethod(): array
    {
        $controller = new BookingController();
        $method = (new \ReflectionClass($controller))->getMethod('isAvailable');
        $method->setAccessible(true);

        return [$controller, $method];
    }

    /**
     * Daftar aula yang kosong pada tanggal (dan sesi opsional) tertentu.
     */
    private function listAvailableAulas(\Illuminate\Contracts\Auth\Authenticatable $user, string $tanggal, ?string $sesi): string
    {
        $aulas = Aula::all(['id', 'nama', 'lokasi']);

        if ($aulas->isEmpty()) {
            return 'Belum ada data aula di database.';
        }

        $bookingController = new BookingController();
        $method = (new \ReflectionClass($bookingController))->getMethod('isAvailable');
        $method->setAccessible(true);

        $sesiList = in_array($sesi, ['pagi', 'siang', 'seharian'], true) ? [$sesi] : ['pagi', 'siang', 'seharian'];

        $lines = [];
        foreach ($aulas as $aula) {
            $free = [];
            foreach ($sesiList as $s) {
                $request = $this->support->makeRequest([
                    'aula_id' => $aula->id,
                    'tanggal_booking' => $tanggal,
                    'sesi_waktu' => $s,
                ], $user);

                if ($method->invoke($bookingController, $request)) {
                    $free[] = $s;
                }
            }

            if ($free !== []) {
                $lines[] = "- {$aula->nama} (ID {$aula->id}): kosong sesi ".implode(', ', $free);
            }
        }

        $date = $this->dates->parse($tanggal);
        $label = $date ? $this->dates->formatIndo($date) : $tanggal;
        $sesiLabel = count($sesiList) === 1 ? " sesi {$sesiList[0]}" : '';

        if ($lines === []) {
            return "Tidak ada aula yang kosong pada {$label}{$sesiLabel}. Semua aula sudah dibooking.";
        }

        return 'Ada '.count($lines)." aula yang kosong pada {$label}{$sesiLabel}:\n".implode("\n", $lines);
    }
}
