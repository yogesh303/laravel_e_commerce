<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_quantity_id')->nullable()->constrained('product_quantity_prices')->nullOnDelete();
            $table->integer('tier_qty')->nullable();     // snapshot: units per batch, e.g. 10
            $table->decimal('tier_price', 10, 2)->nullable(); // snapshot: price per batch
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_quantity_id');
            $table->dropColumn(['tier_qty', 'tier_price']);
        });
    }
};
