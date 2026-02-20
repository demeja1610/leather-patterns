<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_pattern_tag', function (Blueprint $table): void {
            $table->unique(columns: ['pattern_id', 'pattern_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pattern_pattern_tag', function (Blueprint $table): void {
            $table->dropUnique(index: ['pattern_id', 'pattern_tag_id']);
        });
    }
};
