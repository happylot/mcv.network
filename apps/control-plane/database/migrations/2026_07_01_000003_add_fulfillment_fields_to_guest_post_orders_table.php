<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_post_orders', function (Blueprint $table) {
            $table->string('published_url')->nullable()->after('content_requirements');
            $table->text('publisher_notes')->nullable()->after('published_url');
            $table->timestamp('submitted_at')->nullable()->after('publisher_notes');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->foreignId('approved_by_account_id')->nullable()->after('approved_at')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guest_post_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_account_id');
            $table->dropColumn(['published_url', 'publisher_notes', 'submitted_at', 'approved_at']);
        });
    }
};
