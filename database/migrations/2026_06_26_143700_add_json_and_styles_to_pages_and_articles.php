<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (! Schema::hasColumn('pages', 'json')) {
                $table->longText('json')->nullable()->after('contents');
            }

            if (! Schema::hasColumn('pages', 'styles')) {
                $table->longText('styles')->nullable()->after('json');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (! Schema::hasColumn('articles', 'json')) {
                $table->longText('json')->nullable()->after('contents');
            }

            if (! Schema::hasColumn('articles', 'styles')) {
                $table->longText('styles')->nullable()->after('json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (Schema::hasColumn('pages', 'styles')) {
                $table->dropColumn('styles');
            }

            if (Schema::hasColumn('pages', 'json')) {
                $table->dropColumn('json');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'styles')) {
                $table->dropColumn('styles');
            }

            if (Schema::hasColumn('articles', 'json')) {
                $table->dropColumn('json');
            }
        });
    }
};
