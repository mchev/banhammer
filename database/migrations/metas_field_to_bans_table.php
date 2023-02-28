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
        if (!Schema::hasColumn('bans_tables', 'metas')) {
            Schema::table('bans_tables', function (Blueprint $table) {
                $table->json('metas')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bans_tables', 'metas')) {
            Schema::table('bans_tables', function (Blueprint $table) {
                $table->dropColumn('metas');
            });
        }
    }
};
