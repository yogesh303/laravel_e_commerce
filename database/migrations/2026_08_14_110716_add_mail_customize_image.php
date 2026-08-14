<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('logo_images')->nullable()->after('custom_images');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('logo_images')->nullable()->after('custom_images');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('logo_images');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('logo_images');
        });
    }
};
