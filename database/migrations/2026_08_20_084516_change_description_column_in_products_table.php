<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_quantity_prices', function (Blueprint $table) {
            // How many pieces the stepper +/- buttons add/remove for this tier.
            // Null/0 = fall back to old behavior (step by the tier's own quantity).
            $table->unsignedInteger('step')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('product_quantity_prices', function (Blueprint $table) {
            $table->dropColumn('step');
        });
    }
};