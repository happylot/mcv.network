<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('agency_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('agency_service_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('status')->default('pending_agency');
            $table->text('brief');
            $table->string('reference_url')->nullable();
            $table->string('delivery_url')->nullable();
            $table->text('agency_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['client_account_id', 'status']);
            $table->index(['agency_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_service_orders');
    }
};
