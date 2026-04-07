<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed a default profile for every existing user
        DB::table('users')->orderBy('id')->chunk(200, function ($users) {
            foreach ($users as $user) {
                DB::table('profiles')->insert([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'bio' => $user->bio,
                    'avatar' => $user->avatar,
                    'theme' => $user->theme,
                    'custom_domain' => $user->custom_domain,
                    'badge_available_for_hire' => $user->badge_available_for_hire,
                    'badge_verified' => $user->badge_verified,
                    'is_default' => true,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        });

        // 2. Add profile_id to links (nullable first so we can backfill)
        Schema::table('links', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });

        // 3. Add profile_id to profile_views
        Schema::table('profile_views', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->after('user_id')->constrained('profiles')->cascadeOnDelete();
        });

        // 4. Populate from each user's default profile
        DB::statement('UPDATE links SET profile_id = (
            SELECT profiles.id FROM profiles
            WHERE profiles.user_id = links.user_id AND profiles.is_default = 1
            LIMIT 1
        )');

        DB::statement('UPDATE profile_views SET profile_id = (
            SELECT profiles.id FROM profiles
            WHERE profiles.user_id = profile_views.user_id AND profiles.is_default = 1
            LIMIT 1
        )');

        // 5. Make non-nullable now that all rows are populated
        Schema::table('links', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable(false)->change();
        });

        Schema::table('profile_views', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('profile_views', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });

        DB::table('profiles')->delete();
    }
};
