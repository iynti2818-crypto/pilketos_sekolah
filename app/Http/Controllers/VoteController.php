<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function candidates()
    {
        $candidates = Candidate::orderBy('nomor_urut')->get();

        return view('vote.candidates', compact('candidates'));
    }

    // Fungsi untuk menampilkan halaman daftar kandidat
    public function index()
    {
        abort_if(Auth::user()->isAdmin(), 403);

        $candidates = Candidate::orderBy('nomor_urut')->get();

        return view('vote.index', compact('candidates'));
    }

    // Fungsi untuk memproses suara (vote)
    public function store(Request $request)
    {
        abort_if(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
        ], [
            'candidate_id.required' => 'Silakan pilih pasangan calon.',
            'candidate_id.exists' => 'Pasangan calon yang dipilih tidak tersedia.',
        ]);

        $saved = DB::transaction(function () use ($data) {
            $user = User::query()->lockForUpdate()->findOrFail(Auth::id());

            if ($user->has_voted || Vote::where('user_id', $user->id)->exists()) {
                return false;
            }

            Vote::create([
                'user_id' => $user->id,
                'candidate_id' => $data['candidate_id'],
            ]);
            $user->update(['has_voted' => true]);

            return true;
        });

        if (! $saved) {
            return redirect()->back()->with('error', 'Maaf, Anda sudah memberikan suara.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vote.success');
    }

    // Fungsi untuk menampilkan halaman Admin
    public function admin(Request $request)
    {
        $candidates = Candidate::withCount('votes')->orderBy('nomor_urut')->get();
        $totalVotes = $candidates->sum('votes_count');

        $candidates->each(function (Candidate $candidate) use ($totalVotes) {
            $candidate->percentage = $totalVotes > 0
                ? round(($candidate->votes_count / $totalVotes) * 100, 1)
                : 0;
        });

        $totalStudents = User::query()->where('role', 'siswa')->count();
        $votedStudents = User::query()
            ->where('role', 'siswa')
            ->where('has_voted', true)
            ->count();

        $voters = User::query()
            ->where('role', 'siswa')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search) {
                    $query->where('nisn', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('vote.admin', compact('totalVotes', 'candidates', 'totalStudents', 'votedStudents', 'voters'));
    }

    public function adminResults()
    {
        $candidates = Candidate::withCount('votes')->orderBy('nomor_urut')->get();
        $totalVotes = $candidates->sum('votes_count');

        $candidates->each(function (Candidate $candidate) use ($totalVotes) {
            $candidate->percentage = $totalVotes > 0
                ? round(($candidate->votes_count / $totalVotes) * 100, 1)
                : 0;
        });

        return view('vote.admin-results', compact('candidates', 'totalVotes'));
    }

    // Fungsi untuk memproses penambahan Paslon dari Admin
    public function storeCandidate(Request $request)
    {
        $data = $this->validateCandidate($request);
        $data['foto'] = $this->candidatePhoto($request);
        unset($data['foto_url'], $data['foto_file']);
        Candidate::create([...$data, 'visi_misi' => $data['visi']]);

        return redirect('/admin')->with('success', 'Paslon baru berhasil ditambahkan!');
    }

    public function editCandidate(Candidate $candidate)
    {
        return view('vote.edit', compact('candidate'));
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        $data = $this->validateCandidate($request, $candidate);
        $photo = $this->candidatePhoto($request, $candidate->foto);
        unset($data['foto_url'], $data['foto_file']);
        $data['foto'] = $photo;
        $candidate->update([...$data, 'visi_misi' => $data['visi']]);

        return redirect('/admin')->with('success', 'Data pasangan calon berhasil diperbarui.');
    }

    public function destroyCandidate(Candidate $candidate)
    {
        DB::transaction(function () use ($candidate) {
            $voterIds = $candidate->votes()->pluck('user_id');
            User::whereIn('id', $voterIds)->update(['has_voted' => false]);
            $candidate->votes()->delete();
            $candidate->delete();
        });

        return redirect('/admin')->with('success', 'Pasangan calon berhasil dihapus. Status suara siswa yang terdampak telah dibuka kembali.');
    }

    public function resetVote(User $user)
    {
        abort_unless($user->role === 'siswa', 404);

        DB::transaction(function () use ($user) {
            Vote::where('user_id', $user->id)->delete();
            $user->update(['has_voted' => false]);
        });

        return redirect('/admin')->with('success', 'Status suara siswa berhasil direset.');
    }

    public function resetAllVotes()
    {
        DB::transaction(function () {
            Vote::query()->delete();
            User::where('role', 'siswa')->update(['has_voted' => false]);
        });

        return redirect('/admin')->with('success', 'Semua suara berhasil direset.');
    }

    private function validateCandidate(Request $request, ?Candidate $candidate = null): array
    {
        $nomorUrut = $request->input('nomor_urut');
        if (is_string($nomorUrut) && preg_match('/^[0-9]+$/D', $nomorUrut) === 1) {
            $normalizedNomorUrut = ltrim($nomorUrut, '0');
            $request->merge(['nomor_urut' => $normalizedNomorUrut === '' ? '0' : $normalizedNomorUrut]);
        }

        return $request->validate([
            'nomor_urut' => ['required', 'integer', 'min:1', Rule::unique('candidates', 'nomor_urut')->ignore($candidate?->id)],
            'nama_ketua' => ['required', 'string', 'max:255'],
            'nama_wakil' => ['required', 'string', 'max:255'],
            'visi' => ['required', 'string'],
            'misi' => ['required', 'string'],
            'foto_url' => ['nullable', 'url', 'max:2048'],
            'foto_file' => ['nullable', 'image', 'max:5120'],
        ], [
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'nomor_urut.integer' => 'Nomor urut harus berupa angka.',
            'nomor_urut.min' => 'Nomor urut minimal 1.',
            'nomor_urut.unique' => 'Nomor urut sudah digunakan.',
            'nama_ketua.required' => 'Nama ketua wajib diisi.',
            'nama_wakil.required' => 'Nama wakil wajib diisi.',
            'visi.required' => 'Visi wajib diisi.',
            'misi.required' => 'Misi wajib diisi.',
            'foto_url.url' => 'Foto harus berupa alamat URL yang valid.',
            'foto_url.max' => 'Alamat URL foto tidak boleh lebih dari 2048 karakter.',
            'foto_file.image' => 'Berkas yang dipilih harus berupa gambar.',
            'foto_file.max' => 'Ukuran foto tidak boleh lebih dari 5 MB.',
        ]);
    }

    private function candidatePhoto(Request $request, ?string $currentPhoto = null): ?string
    {
        if ($request->hasFile('foto_file')) {
            $path = $request->file('foto_file')->storePublicly('candidates', 'public');

            if (! $path) {
                throw new \RuntimeException('Foto paslon gagal disimpan.');
            }

            return $path;
        }

        return $request->input('foto_url') ?: $currentPhoto;
    }

    // Fungsi untuk menampilkan halaman Hasil Live
    public function hasil()
    {
        // Mengambil semua data paslon beserta jumlah suaranya
        $candidates = Candidate::withCount('votes')->orderBy('nomor_urut')->get();

        return view('vote.hasil', compact('candidates'));
    }
}
