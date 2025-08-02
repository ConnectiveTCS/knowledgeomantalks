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
            $table->timestamps();
            $table->string('name');
            #EventPhoto
            $table->string('photo')->nullable();
            #EventVideo
            $table->string('video')->nullable();
            $table->string('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->string('organizer');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('website')->nullable();
            $table->string('social_media')->nullable();
            $table->string('category')->nullable();
            $table->string('tags')->nullable();
            $table->string('status')->default('draft');
            $table->string('visibility')->default('public');
            $table->string('accessibility')->default('none');
            #gps coordinates
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
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
