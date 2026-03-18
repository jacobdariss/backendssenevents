<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // likes: queries filter by (user_id, profile_id, is_like) and group by entertainment_id
        if (!$this->indexExists('likes', 'likes_user_profile_islike_index')) {
            Schema::table('likes', function (Blueprint $table) {
                $table->index(['user_id', 'profile_id', 'is_like'], 'likes_user_profile_islike_index');
            });
        }
        if (!$this->indexExists('likes', 'likes_entertainment_islike_index')) {
            Schema::table('likes', function (Blueprint $table) {
                $table->index(['entertainment_id', 'is_like'], 'likes_entertainment_islike_index');
            });
        }

        // entertainment_views: queries filter/group by user_id, profile_id, entertainment_id
        if (!$this->indexExists('entertainment_views', 'entviews_user_profile_index')) {
            Schema::table('entertainment_views', function (Blueprint $table) {
                $table->index(['user_id', 'profile_id'], 'entviews_user_profile_index');
            });
        }
        if (!$this->indexExists('entertainment_views', 'entviews_entertainment_index')) {
            Schema::table('entertainment_views', function (Blueprint $table) {
                $table->index('entertainment_id', 'entviews_entertainment_index');
            });
        }

        // entertainment_country_mapping: queries filter by country_id
        if (!$this->indexExists('entertainment_country_mapping', 'entcountry_country_index')) {
            Schema::table('entertainment_country_mapping', function (Blueprint $table) {
                $table->index('country_id', 'entcountry_country_index');
            });
        }

        // pay_per_views: queries filter by user_id
        if (!$this->indexExists('pay_per_views', 'ppv_user_index')) {
            Schema::table('pay_per_views', function (Blueprint $table) {
                $table->index('user_id', 'ppv_user_index');
            });
        }

        // banners: add banner_for to the existing status index coverage
        if (!$this->indexExists('banners', 'banners_banner_for_status_index')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->index(['banner_for', 'status'], 'banners_banner_for_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('likes_user_profile_islike_index');
            $table->dropIndex('likes_entertainment_islike_index');
        });
        Schema::table('entertainment_views', function (Blueprint $table) {
            $table->dropIndex('entviews_user_profile_index');
            $table->dropIndex('entviews_entertainment_index');
        });
        Schema::table('entertainment_country_mapping', function (Blueprint $table) {
            $table->dropIndex('entcountry_country_index');
        });
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->dropIndex('ppv_user_index');
        });
        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('banners_banner_for_status_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return (bool) DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
    }
};
