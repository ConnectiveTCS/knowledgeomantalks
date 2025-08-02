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
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('skills')->nullable();
            $table->string('availability')->nullable();
            $table->text('interests')->nullable();
            $table->text('notes')->nullable();
            $table->string('cv_resume')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->default('pending');
            $table->string('referral_source')->nullable();
            $table->string('background_check_status')->default('not_started');
            $table->string('background_check_date')->nullable();
            $table->string('background_check_notes')->nullable();
            $table->string('training_status')->default('not_started');
            $table->string('training_date')->nullable();
            $table->string('training_notes')->nullable();
            $table->string('orientation_status')->default('not_started');
            $table->string('orientation_date')->nullable();
            $table->string('orientation_notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('medical_conditions')->nullable();
            $table->string('allergies')->nullable();
            $table->string('languages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
