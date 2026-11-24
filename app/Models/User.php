<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'kelas',
        'interested_candidate_id',
        'email',
        'password',
        'has_voted',
        'role',
        'nis',
        'nisn',
        'birth_date',
    ];

    /**
     * Atribut yang disembunyikan saat data diserialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Menentukan tipe data atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_voted' => 'boolean',
            'birth_date' => 'date:Y-m-d',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
