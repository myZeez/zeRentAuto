<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->string('id', 128)->primary();
            $table->string('name', 100);
            $table->text('image');
            $table->unsignedInteger('price');
            $table->string('category', 80);
            $table->string('specs', 200);
            $table->unsignedTinyInteger('seats');
            $table->unsignedSmallInteger('year');
            $table->string('city', 20)->index();
            $table->string('type', 20)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
