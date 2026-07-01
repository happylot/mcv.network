<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('title');
            $table->string('category');
            $table->text('description');
            $table->text('deliverables')->nullable();
            $table->unsignedBigInteger('base_price_cents');
            $table->unsignedTinyInteger('turnaround_days')->default(7);
            $table->string('status')->default('pending_review');
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index(['agency_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_services');
    }
};
