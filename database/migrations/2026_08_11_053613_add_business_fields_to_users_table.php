<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['personal', 'business'])->default('personal')->after('role');
            $table->string('company_name')->nullable()->after('account_type');
            $table->string('industry')->nullable()->after('company_name');
            $table->string('gst_no')->nullable()->after('industry');
            $table->string('mobile_number', 20)->nullable()->unique()->after('gst_no');
            $table->timestamp('mobile_verified_at')->nullable()->after('mobile_number');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'company_name', 'industry', 'mobile_number', 'mobile_verified_at']);
        });
    }
};