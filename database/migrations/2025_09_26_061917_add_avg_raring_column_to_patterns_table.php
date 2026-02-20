<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->float(column: 'avg_rating')->default(0)->after('author_id');
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->dropColumn(columns: 'avg_rating');
        });
    }
};
