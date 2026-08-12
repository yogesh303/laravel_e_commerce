<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_xx_xx_add_quantity_tier_to_order_items_table.php
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_quantity_id')->nullable()->constrained('product_quantity_prices')->nullOnDelete();
            $table->integer('tier_qty')->nullable();
            $table->decimal('tier_price', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_quantity_id');
            $table->dropColumn(['tier_qty', 'tier_price']);
        });
    }
};
