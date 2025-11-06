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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('timezone', [
                'UTC-08:00',
                'UTC-06:00',
                'UTC-03:00',
                'UTC±00:00',
                'UTC+01:00',
                'UTC+03:00',
                'UTC+05:30',
                'UTC+08:00',
                'UTC+09:00',
                'UTC+12:00',
            ]);
            $table->text('short_description');
            $table->enum('category', ['workshop', 'conference', 'webinar']);
            $table->enum('repeat', ['na', 'daily', 'weekly', 'monthly', 'annually', 'custom']);
            $table->text('content');
            $table->string('location');
            $table->string('register_link');
            $table->string('thumbnail');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
