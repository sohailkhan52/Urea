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
        Schema::create('welcome_page_settings', function (Blueprint $table) {
            $table->id();

            // Company & Branding
            $table->string('company_name')->default('Fertilizer Management System');
            $table->string('company_short_name')->nullable();
            $table->string('company_logo')->nullable();

            // Hero Section
            $table->string('hero_title')->default('Welcome to Fertilizer Management System');
            $table->text('hero_description')->nullable();
            $table->string('hero_background_image')->nullable();
            $table->string('hero_primary_button_text')->default('Get Started');
            $table->string('hero_primary_button_url')->nullable();
            $table->string('hero_secondary_button_text')->nullable();
            $table->string('hero_secondary_button_url')->nullable();
            $table->boolean('show_hero_section')->default(true);

            // Features Section
            $table->string('features_section_title')->default('Key Features');
            $table->text('features_section_description')->nullable();

            // Workflow Section
            $table->string('workflow_section_title')->default('How It Works');
            $table->text('workflow_section_description')->nullable();

            // CTA Section
            $table->string('cta_title')->default('Ready to Get Started?');
            $table->text('cta_description')->nullable();
            $table->string('cta_button_text')->default('Start Now');
            $table->string('cta_button_url')->nullable();
            $table->boolean('show_cta_section')->default(true);

            // Footer
            $table->string('footer_company_name')->default('Fertilizer Management System');
            $table->text('footer_description')->nullable();
            $table->string('copyright_text')->default('© 2024 All rights reserved.');
            $table->boolean('show_footer_links')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index for faster lookup
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_page_settings');
    }
};
