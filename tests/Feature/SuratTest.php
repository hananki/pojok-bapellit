<?php

namespace Tests\Feature;

use App\Models\PengaturanSurat;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuratTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Setup setting awal surat
        PengaturanSurat::create([
            'id' => 1,
            'kode_surat' => 'BAPELIT',
            'nomor_terakhir' => 10,
            'format' => '{nomor}',
        ]);
    }

    public function test_preview_nomor_returns_next_available_number(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->getJson(route('surat.preview-nomor'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'nomor' => '11',
            ]);
    }

    public function test_first_user_successfully_takes_nomor_surat(): void
    {
        $user = User::create([
            'name' => 'User Pertama',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $file = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('surat.store'), [
            'isi_surat' => 'Isi surat pertama',
            'file_surat' => $file,
            'no_surat_hidden' => '11',
        ]);

        $response->assertRedirect(route('surat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('surats', [
            'user_id' => $user->id,
            'no_surat' => '11',
            'isi_surat' => 'Isi surat pertama',
        ]);

        $this->assertEquals(11, PengaturanSurat::find(1)->getCounter());
    }

    public function test_second_user_fails_when_number_is_already_taken_by_first_user(): void
    {
        $user1 = User::create([
            'name' => 'User Pertama',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => 'User Kedua',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $file1 = UploadedFile::fake()->create('surat1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('surat2.pdf', 100, 'application/pdf');

        // User 1 menyimpan lebih dulu nomor 11
        $this->actingAs($user1)->post(route('surat.store'), [
            'isi_surat' => 'Isi surat user 1',
            'file_surat' => $file1,
            'no_surat_hidden' => '11',
        ]);

        // User 2 mencoba menyimpan dengan nomor 11 yang sempat tampil di layarnya
        $response = $this->actingAs($user2)->from(route('surat.index'))->post(route('surat.store'), [
            'isi_surat' => 'Isi surat user 2',
            'file_surat' => $file2,
            'no_surat_hidden' => '11',
        ]);

        // User 2 harus gagal dan diredirect kembali dengan pesan error notifikasi
        $response->assertRedirect(route('surat.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah diambil oleh pengguna lain yang menyimpan lebih dulu', session('error'));

        // Pastikan hanya surat user 1 yang tersimpan dengan nomor 11
        $this->assertEquals(1, Surat::where('no_surat', '11')->count());
        $this->assertEquals($user1->id, Surat::where('no_surat', '11')->first()->user_id);
    }
}
