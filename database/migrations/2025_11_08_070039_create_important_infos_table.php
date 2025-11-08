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
        Schema::create('important_infos', function (Blueprint $table) {
            $table->id();
            $table->string('first_title')->nullable();
            $table->text('first_description')->nullable();
            $table->string('first_image')->nullable();

            $table->string('second_title')->nullable();
            $table->text('second_description')->nullable();
            $table->string('second_image')->nullable();

            $table->string('third_title')->nullable();
            $table->text('third_description')->nullable();
            $table->string('third_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('important_infos');
    }
};
