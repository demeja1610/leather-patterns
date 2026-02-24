<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_reviews', function (Blueprint $table) {
            DB::table('pattern_reviews')
                ->update(['created_at' => DB::raw('reviewed_at')]);

            $table->dropColumn('reviewed_at');
        });
    }


    public function down(): void
    {
        Schema::table('pattern_reviews', function (Blueprint $table) {
            $table->timestamp(column: 'reviewed_at')->after('comment');
        });

        DB::table('pattern_reviews')
            ->update(['reviewed_at' => DB::raw('created_at')]);
    }
};
