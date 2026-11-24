<?php

use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $userIdsWithVotes = Vote::query()->distinct()->pluck('user_id');

        User::query()->whereIn('id', $userIdsWithVotes)->update(['has_voted' => true]);
        User::query()->whereNotIn('id', $userIdsWithVotes)->update(['has_voted' => false]);
    }

    public function down(): void
    {
    }
};