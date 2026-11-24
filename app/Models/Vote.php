<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'candidate_id'];

    // Relasi: Satu vote milik satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu vote untuk satu kandidat
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}