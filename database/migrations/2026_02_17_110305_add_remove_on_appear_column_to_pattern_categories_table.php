<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->boolean(column: 'remove_on_appear')->default(false)->after('replace_id');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->dropColumn(columns: 'remove_on_appear');
        });
    }
};
