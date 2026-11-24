<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = ['nomor_urut', 'nama_ketua', 'nama_wakil', 'visi', 'misi', 'visi_misi', 'foto'];

    // Relasi: Satu kandidat bisa memiliki banyak suara (votes)
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function photoUrl(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return Str::startsWith($this->foto, ['http://', 'https://'])
            ? $this->foto
            : '/storage/'.ltrim($this->foto, '/');
    }
}
