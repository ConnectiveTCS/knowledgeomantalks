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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('organization_name');
            $table->string('contact_name');
            $table->string('contact_email')->unique();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('active');
            $table->string('type')->default('non-profit');
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('partnership_level')->default('basic');
            $table->string('partnership_start_date')->nullable();
            $table->string('partnership_end_date')->nullable();
            $table->string('partnership_renewal_date')->nullable();
            $table->string('partnership_renewal_status')->default('pending');
            $table->string('partnership_renewal_notes')->nullable();
            $table->string('partnership_renewal_approval')->default('pending');
            $table->string('partnership_renewal_approval_notes')->nullable();
            $table->string('partnership_renewal_approval_date')->nullable();
            $table->string('partnership_renewal_approval_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
