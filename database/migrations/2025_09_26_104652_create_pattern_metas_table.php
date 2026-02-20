<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pattern_metas', function (Blueprint $table): void {
            $table->id();

            $table->boolean(column: 'pattern_downloaded')->default(false);
            $table->boolean(column: 'images_downloaded')->default(false);
            $table->timestamp(column: 'reviews_updated_at')->nullable();

            $table->unsignedBigInteger(column: 'pattern_id')->unique();
            $table->foreign(columns: 'pattern_id')
                ->references('id')
                ->on('patterns')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_metas');
    }
};
