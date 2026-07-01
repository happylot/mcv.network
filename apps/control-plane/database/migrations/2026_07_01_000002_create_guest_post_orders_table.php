<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_post_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('publisher_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('publisher_website_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('status')->default('pending_publisher');
            $table->string('target_url');
            $table->string('anchor_text')->nullable();
            $table->string('article_title')->nullable();
            $table->text('content_requirements')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['advertiser_account_id', 'status']);
            $table->index(['publisher_account_id', 'status']);
            $table->index(['publisher_website_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_post_orders');
    }
};
