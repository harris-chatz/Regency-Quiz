<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // PII
            $table->string('name', 150);
            $table->string('email', 150)->index();
            $table->string('phone', 20)->index();

            // Consents
            $table->boolean('age_consent')->default(false);
            $table->boolean('terms_consent')->default(false);
            $table->boolean('marketing_consent')->default(false);

            // Quiz outcome
            $table->enum('persona_color', ['green', 'yellow', 'pink'])->nullable();
            $table->boolean('has_visited_casino')->nullable();
            $table->timestamp('quiz_started_at')->nullable();
            $table->json('answers')->nullable();

            // Redemption
            $table->string('redemption_code', 64)->nullable()->index();

            // Tracking
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
