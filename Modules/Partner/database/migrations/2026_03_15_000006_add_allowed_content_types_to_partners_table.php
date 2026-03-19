<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('partners', 'allowed_content_types')) {
        Schema::table('partners', function (Blueprint $table) {
            $table->json('allowed_content_types')->nullable()->after('status');
        });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('partners', 'allowed_content_types')) {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('allowed_content_types');
        });
    }
};
