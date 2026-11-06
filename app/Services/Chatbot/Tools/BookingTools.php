<?php

namespace App\Services\Chatbot\Tools;

use App\Http\Controllers\BookingController;
use App\Models\Aula;
use App\Models\Booking;
use App\Services\Chatbot\Support\DateParser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Tool AI untuk BOOKING AULA.
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
                    'description' => 'Lihat SEMUA aula yang kosong pada tanggal tertentu (atau besok).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tanggal_booking' => ['type' => 'string', 'description' => 'Format YYYY-MM-DD'],
                            'sesi_waktu' => ['type' => 'string', 'enum' => ['pagi', 'siang', 'seharian']],
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
        try {
            return match ($name) {
                'check_booking_availability' => $this->checkAvailability($arguments, $user),
                'list_available_aulas' => $this->listAvailable($arguments, $user),
                'create_booking' => $this->createBooking($arguments, $user),
                'get_riwayat_booking' => $this->history($user),
                default => $this->fail("Fungsi tidak dikenal: {$name}"),
            };
        } catch (Throwable $e) {
            Log::error('[BookingTools] Exception', [
                'tool' => $name,
                'message' => $e->getMessage(),
                'arguments' => $arguments,
            ]);
            return $this->fail('Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    public function describe(string $name, array $arguments, string $result): string
    {
        // [FIX] Cukup return string aslinya. Jangan ditambahi teks apapun.
        // PromptBuilder (followUp) yang akan merangkai ini jadi kalimat konfirmasi yang rapi.
        return $result;
    }

    // ── Implementasi tiap tool ───────────────────────────────────────

    private function checkAvailability(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $this->fail($dateError);
        }

        try {
            $request = $this->support->makeRequest($arguments, $user);
            $request->validate([
                'aula_id' => 'required|exists:aulas,id',
                'tanggal_booking' => 'required|date',
                'sesi_waktu' => 'required|in:pagi,siang,seharian',
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Data tidak valid: '.implode(', ', $e->validator->errors()->all()));
        }

        [$controller, $method] = $this->availabilityMethod();
        $available = $method->invoke($controller, $request);

        // [FIX] Super singkat biar ga buang token & ga bikin AI ngulang-ngulang.
        if ($available) {
            return $this->ok('Tersedia');
        }

        return $this->fail('Sudah dibooking pada waktu tersebut.');
    }

    private function listAvailable(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $this->fail($dateError);
        }

        try {
            return $this->listAvailableAulas($user, $arguments['tanggal_booking'], $arguments['sesi_waktu'] ?? null);
        } catch (Throwable $e) {
            Log::error('[BookingTools] list_available_aulas error', ['message' => $e->getMessage()]);
            return $this->fail('Gagal mengambil daftar aula: '.$e->getMessage());
        }
    }

    private function createBooking(array &$arguments, Authenticatable $user): string
    {
        if ($dateError = $this->dates->resolveBookingDate($arguments)) {
            return $this->fail($dateError);
        }

        // Pre-validasi field wajib sebelum sentuh DB
        $requiredFields = ['aula_id', 'tanggal_booking', 'nama_penanggung_jawab', 'keperluan', 'jumlah_peserta', 'sesi_waktu'];
        foreach ($requiredFields as $field) {
            if (empty($arguments[$field])) {
                return $this->fail("Data {$field} belum lengkap. Mohon lengkapi dulu.");
            }
        }

        // Cek ketersediaan dulu sebelum create (supaya tidak error di tengah jalan)
        try {
            [$controller, $method] = $this->availabilityMethod();
            $availReq = $this->support->makeRequest([
                'aula_id' => $arguments['aula_id'],
                'tanggal_booking' => $arguments['tanggal_booking'],
                'sesi_waktu' => $arguments['sesi_waktu'],
            ], $user);

            if (! $method->invoke($controller, $availReq)) {
                return $this->fail('Gagal: Aula sudah dibooking pada sesi tersebut. Silakan pilih waktu lain.');
            }
        } catch (Throwable $e) {
            Log::warning('[BookingTools] Pre-check availability gagal', ['message' => $e->getMessage()]);
        }

        try {
            $request = $this->support->makeRequest($arguments, $user);
            $request->merge(['user_id' => $user->getAuthIdentifier()]);

            $response = (new BookingController())->store($request);

            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                $errors = session()->pull('errors');
                if ($errors && $errors->any()) {
                    return $this->fail('Booking gagal: '.implode(', ', $errors->all()));
                }

                $status = session()->pull('status');
                $success = session()->pull('success');

                if ($success || $status) {
                    return $this->ok((string) ($success ?? $status ?? 'Booking berhasil dibuat. Status saat ini Pending, menunggu persetujuan admin.'));
                }
            }

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                if ($response->getStatusCode() >= 400 || (isset($data['success']) && $data['success'] === false)) {
                    return $this->fail($data['message'] ?? 'Booking gagal diproses.');
                }
                return $this->ok($data['message'] ?? 'Booking berhasil dibuat. Status saat ini Pending, menunggu persetujuan admin.');
            }

            $summary = $this->support->summarize($response, 'Booking berhasil dibuat. Status saat ini Pending, menunggu persetujuan admin.');

            $lower = mb_strtolower($summary);
            if (str_contains($lower, 'gagal') || str_contains($lower, 'error') || str_contains($lower, 'tidak valid')) {
                return $this->fail($summary);
            }

            return $this->ok($summary);
        } catch (ValidationException $e) {
            return $this->fail('Data booking tidak valid: '.implode(', ', $e->validator->errors()->all()));
        } catch (Throwable $e) {
            Log::error('[BookingTools] create_booking exception', [
                'message' => $e->getMessage(),
                'arguments' => $arguments,
            ]);
            return $this->fail('Booking gagal: '.$e->getMessage());
        }
    }

    private function history(Authenticatable $user): string
    {
        $bookings = Booking::where('user_id', $user->getAuthIdentifier())
            ->with('aula')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($bookings->isEmpty()) {
            return $this->ok('Belum ada riwayat booking.');
        }

        $lines = $bookings->map(fn ($b) => '- '.($b->aula?->nama ?? 'Aula tidak ditemukan')." pada {$b->tanggal_booking} ({$b->status})")->implode("\n");

        return $this->ok("Berikut 5 riwayat booking terakhir Anda:\n" . $lines);
    }

    // ── Helper ──────────────────────────────────────────────────────

    private function availabilityMethod(): array
    {
        $controller = new BookingController();
        $method = (new \ReflectionClass($controller))->getMethod('isAvailable');
        $method->setAccessible(true);

        return [$controller, $method];
    }

    private function listAvailableAulas(Authenticatable $user, string $tanggal, ?string $sesi): string
    {
        $aulas = Aula::all(['id', 'nama', 'lokasi']);

        if ($aulas->isEmpty()) {
            return $this->fail('Belum ada data aula di database.');
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
            return $this->fail("Tidak ada aula yang kosong pada {$label}{$sesiLabel}. Semua aula sudah dibooking.");
        }

        return $this->ok('Ada '.count($lines)." aula yang kosong pada {$label}{$sesiLabel}:\n".implode("\n", $lines));
    }

    private function ok(string $message): string
    {
        return json_encode([
            'success' => true,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function fail(string $error): string
    {
        return json_encode([
            'success' => false,
            'error' => $error,
        ], JSON_UNESCAPED_UNICODE);
    }
}