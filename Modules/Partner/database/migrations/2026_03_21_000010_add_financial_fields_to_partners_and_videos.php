<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Commission sur la fiche partenaire (nullable = à définir plus tard)
        Schema::table('partners', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('status')
                ->comment('% retenu par la plateforme ex: 30.00');
            $table->string('revenue_model', 20)->default('flat')->after('commission_rate')
                ->comment('flat | tiered');
        });

        // Prix proposé par le partenaire (conservé même si l'admin modifie le prix final)
        Schema::table('videos', function (Blueprint $table) {
            $table->decimal('partner_proposed_price', 10, 2)->nullable()->after('price')
                ->comment('Prix soumis par le partenaire avant validation admin');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'revenue_model']);
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('partner_proposed_price');
        });
    }
};
