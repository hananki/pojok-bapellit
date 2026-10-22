<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun
     * Support: search, filter bidang, filter jabatan
     * Pagination: 25 per halaman
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterBidang = $request->input('bidang');
        $filterJabatan = $request->input('jabatan');
        $filterRole = $request->input('role');
        $filterStatus = $request->input('status');

        $users = User::when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        })
            ->when($filterBidang, function ($q, $bidang) {
                if ($bidang === '__tanpa_bidang__') {
                    return $q->where(function ($query) {
                        $query->whereNull('bidang')->orWhere('bidang', '');
                    });
                }

                return $q->where('bidang', $bidang);
            })
            ->when($filterJabatan, function ($q, $jabatan) {
                if ($jabatan === '__tanpa_jabatan__') {
                    return $q->where(function ($query) {
                        $query->whereNull('jabatan')->orWhere('jabatan', '');
                    });
                }

                return $q->where('jabatan', $jabatan);
            })
            ->when($filterRole, fn ($q, $role) => $q->where('role', $role))
            ->when($filterStatus, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('bidang')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // Ambil daftar jabatan unik untuk dropdown filter
        $jabatanList = User::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan');

        // Ambil daftar bidang unik untuk dropdown filter
        $bidangList = User::whereNotNull('bidang')
            ->where('bidang', '!=', '')
            ->distinct()
            ->orderBy('bidang')
            ->pluck('bidang');

        return view('admin.kelolaakun.index', compact(
            'users',
            'search',
            'filterBidang',
            'filterJabatan',
            'filterRole',
            'filterStatus',
            'jabatanList',
            'bidangList'
        ));
    }

    public function create()
    {
        return view('admin.kelolaakun.create', [
            'user' => null,
            'bidangList' => $this->getDistinctUserValues('bidang'),
            'jabatanList' => $this->getDistinctUserValues('jabatan'),
        ]);
    }

    /**
     * Ambil nilai unik dari data akun yang sudah tersimpan.
     */
    private function getDistinctUserValues(string $column)
    {
        return User::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    /**
     * Simpan akun baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'nip' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:255',
            'golongan' => 'nullable|string|max:50',
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'pengelola_surat', 'pengelola_aula', 'pengelola_absensi'])],
            'bidang' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'golongan' => $validated['golongan'] ?? null,
            'role' => $validated['role'],
            'bidang' => $validated['bidang'] ?? null,
            'status' => $validated['status'],
            'password' => ! empty($validated['password']) ? $validated['password'] : null,
            // Jika role adalah admin, set is_admin = true
            'is_admin' => $validated['role'] === 'admin',
        ]);

        return redirect()
            ->route('admin.kelolaakun.index')
            ->with('success', 'Akun baru berhasil ditambahkan!');
    }

    /**
     * Form edit akun (pakai view yang sama dengan create)
     */
    public function edit(int $id)
    {
        $user = User::findOrFail($id);

        return view('admin.kelolaakun.create', [
            'user' => $user,
            'bidangList' => $this->getDistinctUserValues('bidang'),
            'jabatanList' => $this->getDistinctUserValues('jabatan'),
        ]);
    }

    /**
     * Update data akun di database
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'nip' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:255',
            'golongan' => 'nullable|string|max:50',
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'pengelola_surat', 'pengelola_aula', 'pengelola_absensi'])],
            'bidang' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'] ?? null;
        $user->email = $validated['email'] ?? null;
        $user->nip = $validated['nip'] ?? null;
        $user->whatsapp = $validated['whatsapp'] ?? null;
        $user->jabatan = $validated['jabatan'] ?? null;
        $user->golongan = $validated['golongan'] ?? null;
        $user->role = $validated['role'];
        $user->bidang = $validated['bidang'] ?? null;
        $user->status = $validated['status'];
        // Jika role adalah admin, set is_admin = true
        $user->is_admin = $validated['role'] === 'admin';

        // Update password hanya kalau diisi
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.kelolaakun.index')
            ->with('success', 'Data akun berhasil diperbarui!');
    }

    /**
     * Hapus akun
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        // Cegah admin menghapus akunnya sendiri
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.kelolaakun.index')
                ->with('error', 'Kamu tidak bisa menghapus akunmu sendiri!');
        }

        $user->delete();

        return redirect()
            ->route('admin.kelolaakun.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}