<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_files', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('pattern_id');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_files', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
};
