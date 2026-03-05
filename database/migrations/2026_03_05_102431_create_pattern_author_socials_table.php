<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_author_socials', function (Blueprint $table) {
            $table->id();

            $table->string('type');
            $table->string('url');
            $table->unsignedBigInteger('author_id');
            $table->boolean('is_published')->default(false)->index();

            $table->timestamps();

            $table->foreign('author_id')
                ->references('id')
                ->on('pattern_authors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_author_socials');
    }
};
