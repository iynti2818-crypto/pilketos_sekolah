<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\VoterImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VoteController::class, 'home'])->name('home');
Route::get('/paslon', [VoteController::class, 'candidates'])->name('candidates');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/siswa/login', [AuthController::class, 'showVoterLogin'])->name('voter.login');
    Route::post('/siswa/login', [AuthController::class, 'loginVoter'])->middleware('throttle:5,1')->name('voter.login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::view('/vote/success', 'vote.success')->name('vote.success');

Route::middleware('voter')->group(function () {
    Route::get('/vote', [VoteController::class, 'index']);
    Route::post('/vote', [VoteController::class, 'store']);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [VoteController::class, 'admin']);
    Route::get('/hasil', [VoteController::class, 'adminResults']);
    Route::post('/pemilih/import', [VoterImportController::class, 'import'])->name('admin.voters.import');
    Route::get('/pemilih/template', [VoterImportController::class, 'template'])->name('admin.voters.template');
    Route::post('/tambah-paslon', [VoteController::class, 'storeCandidate'])->name('admin.candidates.store');
    Route::get('/paslon/{candidate}/edit', [VoteController::class, 'editCandidate']);
    Route::put('/paslon/{candidate}', [VoteController::class, 'updateCandidate']);
    Route::delete('/paslon/{candidate}', [VoteController::class, 'destroyCandidate']);
    Route::delete('/siswa/{user}/reset-suara', [VoteController::class, 'resetVote']);
    Route::delete('/reset-semua-suara', [VoteController::class, 'resetAllVotes']);
});

Route::get('/hasil', [VoteController::class, 'hasil']);
