<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enterprise_access_requests', function (Blueprint $table) {
            $table->id();

            // A) Organization
            $table->string('organization_name');
            $table->string('organization_type');
            $table->string('industry_sector')->nullable(); // if corporate
            $table->string('company_size')->nullable();

            // B) Use case & needs
            $table->string('primary_use_case');
            $table->string('primary_use_case_other')->nullable();

            $table->json('geographic_focus'); // array
            $table->json('focus_states')->nullable(); // array
            $table->text('focus_sectors_regions')->nullable(); // e.g. oil & gas region, border areas
            $table->text('focus_cities_lgas')->nullable(); // freeform or csv

            $table->json('features_of_interest'); // array

            // C) Contact
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('preferred_contact_method');

            // Tracking / attribution
            $table->string('source_page')->nullable();          // e.g. risk-map
            $table->string('attempted_risk_type')->nullable();  // e.g. Kidnapping
            $table->string('attempted_year')->nullable();       // e.g. 2026

            $table->string('status')->default('new'); // new/contacted/qualified/won/lost
            $table->text('internal_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_access_requests');
    }
};
