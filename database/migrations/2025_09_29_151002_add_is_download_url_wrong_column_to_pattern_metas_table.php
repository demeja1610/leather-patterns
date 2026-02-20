<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_metas', function (Blueprint $table): void {
            $table->boolean('is_download_url_wrong')->default(false)->after('reviews_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pattern_metas', function (Blueprint $table): void {
            $table->dropColumn('is_download_url_wrong');
        });
    }
};
