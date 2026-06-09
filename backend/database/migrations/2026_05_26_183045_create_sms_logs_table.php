<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->nullable()
                ->constrained('leads')
                ->nullOnDelete();

            $table->string('provider', 30)->default('apifon');
            $table->string('phone', 20);
            $table->string('sender_id', 30)->nullable();

            // Outbound request & inbound response, plus metadata
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            // queued = job is created but not yet sent (future use with proper queue)
            // sent    = HTTP 2xx from provider
            // failed  = network error or provider returned error
            // dry_run = APIFON_ENABLED=false, no actual HTTP call
            $table->enum('status', ['queued', 'sent', 'failed', 'dry_run'])
                ->default('queued')
                ->index();

            $table->string('error_message', 500)->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
