<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter 'Cloudflare Stream' comme type d'upload si absent
        $exists = DB::table('constants')
            ->where('type', 'upload_type')
            ->where('value', 'CF_Stream')
            ->exists();

        if (!$exists) {
            DB::table('constants')->insert([
                'type'       => 'upload_type',
                'name'       => 'Cloudflare Stream',
                'value'      => 'CF_Stream',
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('constants')
            ->where('type', 'upload_type')
            ->where('value', 'CF_Stream')
            ->delete();
    }
};
