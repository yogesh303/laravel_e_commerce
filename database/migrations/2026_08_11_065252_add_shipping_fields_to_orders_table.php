<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('total_price');
            $table->string('shipping_phone', 20)->nullable()->after('shipping_name');
            $table->string('shipping_address_line1')->nullable()->after('shipping_phone');
            $table->string('shipping_address_line2')->nullable()->after('shipping_address_line1');
            $table->string('shipping_city')->nullable()->after('shipping_address_line2');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_pincode', 10)->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_pincode');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name', 'shipping_phone', 'shipping_address_line1',
                'shipping_address_line2', 'shipping_city', 'shipping_state',
                'shipping_pincode', 'shipping_country',
            ]);
        });
    }
};