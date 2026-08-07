<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');        // e.g. "Size", "Color"
            $table->text('values');        // comma-separated: "S,M,L,XL"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_options');
    }
};