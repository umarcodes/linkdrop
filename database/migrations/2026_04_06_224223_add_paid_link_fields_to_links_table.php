<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->unsignedInteger('price_cents')->nullable()->after('max_clicks');
            $table->string('currency', 3)->nullable()->default('usd')->after('price_cents');
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn(['price_cents', 'currency']);
        });
    }
};
