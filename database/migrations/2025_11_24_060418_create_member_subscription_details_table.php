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
        Schema::create('member_subscription_details', function (Blueprint $table) {
            $table->id();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->boolean('status')->default(1);
            
            $table->string('membership_type')->nullable();
            $table->string('ordinary_membership_plan')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('membership_signature')->nullable();

            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->text('company_profile')->nullable();
            $table->string('office_presence_regions')->nullable();
            $table->text('business_categories')->nullable();
            $table->string('other_business_category')->nullable();
            $table->string('director_name')->nullable();
            $table->date('director_signed_at')->nullable();
            $table->string('signature')->nullable();

            $table->string('lead_contact_name')->nullable();
            $table->string('lead_contact_phone')->nullable();
            $table->string('lead_contact_title')->nullable();
            $table->string('lead_contact_email')->nullable();
            
            $table->string('contact_2_name')->nullable();
            $table->string('contact_2_phone')->nullable();
            $table->string('contact_2_title')->nullable();
            $table->string('contact_2_email')->nullable();
            
            $table->string('contact_3_name')->nullable();
            $table->string('contact_3_phone')->nullable();
            $table->string('contact_3_title')->nullable();
            $table->string('contact_3_email')->nullable();
            
            $table->string('contact_4_name')->nullable();
            $table->string('contact_4_phone')->nullable();
            $table->string('contact_4_title')->nullable();
            $table->string('contact_4_email')->nullable();
            
            $table->string('contact_5_name')->nullable();
            $table->string('contact_5_phone')->nullable();
            $table->string('contact_5_title')->nullable();
            $table->string('contact_5_email')->nullable();

            $table->string('license_officer_1_name')->nullable();
            $table->string('license_officer_1_phone')->nullable();
            $table->string('license_officer_1_title')->nullable();
            $table->string('license_officer_1_email')->nullable();
            $table->string('license_officer_2_name')->nullable();
            $table->string('license_officer_2_phone')->nullable();
            $table->string('license_officer_2_title')->nullable();
            $table->string('license_officer_2_email')->nullable();
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_subscription_details');
    }
};
