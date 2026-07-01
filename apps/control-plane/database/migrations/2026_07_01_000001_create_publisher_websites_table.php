<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publisher_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('name');
            $table->string('niche')->default('General');
            $table->string('language', 16)->default('en');
            $table->char('country', 2)->default('US');
            $table->unsignedInteger('monthly_traffic')->default(0);
            $table->unsignedTinyInteger('domain_rating')->default(0);
            $table->unsignedTinyInteger('domain_authority')->default(0);
            $table->unsignedBigInteger('guest_post_price_cents');
            $table->unsignedTinyInteger('turnaround_days')->default(3);
            $table->text('guidelines')->nullable();
            $table->string('sample_url')->nullable();
            $table->string('status')->default('pending_review');
            $table->timestamps();

            $table->unique(['account_id', 'domain']);
            $table->index(['status', 'niche']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_websites');
    }
};
