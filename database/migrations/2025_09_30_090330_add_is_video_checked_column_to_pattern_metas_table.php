<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pattern_metas', function (Blueprint $table): void {
            $table->boolean(column: 'is_video_checked')->default(false)->after('is_download_url_wrong');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_metas', function (Blueprint $table): void {
            $table->dropColumn(columns: 'is_video_checked');
        });
    }
};
