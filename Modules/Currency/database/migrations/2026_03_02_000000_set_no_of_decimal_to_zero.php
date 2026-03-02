<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('currencies')->update(['no_of_decimal' => 0]);
    }

    public function down(): void
    {
        DB::table('currencies')->update(['no_of_decimal' => 2]);
    }
};
