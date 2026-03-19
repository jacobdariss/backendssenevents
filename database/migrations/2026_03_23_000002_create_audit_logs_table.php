<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('action');           // approve, reject, delete, login, etc.
                $table->string('model_type')->nullable();  // Entertainment, Partner, Video...
                $table->unsignedBigInteger('model_id')->nullable();
                $table->string('model_name')->nullable();  // nom du contenu
                $table->text('details')->nullable();       // JSON avec infos supplémentaires
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['action', 'created_at']);
                $table->index(['model_type', 'model_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
