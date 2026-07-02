<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('can_buy')->default(true)->after('type');
            $table->boolean('can_sell_inventory')->default(false)->after('can_buy');
            $table->boolean('can_sell_services')->default(false)->after('can_sell_inventory');
        });

        DB::table('accounts')->where('type', 'advertiser')->update(['can_buy' => true]);
        DB::table('accounts')->where('type', 'publisher')->update(['can_buy' => true, 'can_sell_inventory' => true]);
        DB::table('accounts')->where('type', 'agency')->update(['can_buy' => true, 'can_sell_services' => true]);
        DB::table('accounts')->where('type', 'admin')->update([
            'can_buy' => true,
            'can_sell_inventory' => true,
            'can_sell_services' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['can_buy', 'can_sell_inventory', 'can_sell_services']);
        });
    }
};
