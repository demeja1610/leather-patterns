<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id')->nullable()->after('id');

            $table->foreign('author_id')->references('id')->on('pattern_authors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });
    }
};
