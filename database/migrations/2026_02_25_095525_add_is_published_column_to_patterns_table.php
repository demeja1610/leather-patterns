<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->boolean(column: 'is_published')->default(false)->after('source_url')->index();
        });
    }


    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropIndex(index: ['is_published']);

            $table->dropColumn(columns: 'is_published');
        });
    }
};
