<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasUniqueIndex = collect(Schema::getIndexes('voter_tokens'))
            ->contains(fn (array $index) => ($index['name'] ?? null) === 'voter_tokens_token_hash_unique');

        if ($hasUniqueIndex) {
            Schema::table('voter_tokens', function (Blueprint $table) {
                $table->dropUnique(['token_hash']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('voter_tokens', function (Blueprint $table) {
            $table->unique('token_hash');
        });
    }
};
