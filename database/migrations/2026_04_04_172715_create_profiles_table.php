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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username', 32)->unique();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->json('theme')->nullable();
            $table->string('custom_domain', 255)->nullable()->unique();
            $table->boolean('badge_available_for_hire')->default(false);
            $table->boolean('badge_verified')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
