<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showVoterLogin()
    {
        return view('auth.voter-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt([...$credentials, 'role' => 'admin'])) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect('/admin');
    }

    public function loginVoter(Request $request)
    {
        $data = $request->validate([
            'nisn' => ['required', 'digits:10'],
            'birth_date' => ['required', 'date_format:Y-m-d'],
        ], [
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.digits' => 'NISN harus terdiri dari 10 angka.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date_format' => 'Format tanggal lahir tidak valid.',
        ]);

        $result = DB::transaction(function () use ($data): array {
            $user = User::query()
                ->where('nisn', $data['nisn'])
                ->where('role', 'siswa')
                ->whereDate('birth_date', $data['birth_date'])
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return ['status' => 'invalid'];
            }

            if ($user->has_voted || Vote::query()->where('user_id', $user->id)->exists()) {
                return ['status' => 'already_voted'];
            }

            return ['status' => 'valid', 'user' => $user];
        });

        if ($result['status'] === 'already_voted') {
            return back()->withErrors([
                'nisn' => 'Hak pilih Anda sudah digunakan dan tidak dapat memilih kembali.',
            ]);
        }

        if ($result['status'] !== 'valid') {
            return back()->withErrors([
                'nisn' => 'NISN atau tanggal lahir tidak valid, atau Anda bukan pemilih terdaftar di sekolah.',
            ]);
        }

        /** @var User $user */
        $user = $result['user'];
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/vote');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda berhasil keluar.');
    }
}
