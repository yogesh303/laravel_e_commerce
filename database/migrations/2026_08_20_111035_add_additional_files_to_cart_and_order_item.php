<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('additional_files')->nullable()->after('additional_file');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('additional_files')->nullable()->after('additional_file');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('additional_files');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('additional_files');
        });
    }
};