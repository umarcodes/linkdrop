<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('badge_available_for_hire')->default(false)->after('api_key');
            $table->boolean('badge_verified')->default(false)->after('badge_available_for_hire');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['badge_available_for_hire', 'badge_verified']);
        });
    }
};
