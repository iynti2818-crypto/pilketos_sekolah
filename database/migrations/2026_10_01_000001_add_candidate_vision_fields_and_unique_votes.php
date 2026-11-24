<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Candidate;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Candidate::query()->whereNull('visi')->each(function (Candidate $candidate) {
            $parts = preg_split('/\s-\s/', $candidate->visi_misi, 2);
            $candidate->update([
                'visi' => $parts[0] ?? $candidate->visi_misi,
                'misi' => $parts[1] ?? '',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['visi', 'misi']);
        });
    }
};