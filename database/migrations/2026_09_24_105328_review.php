<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');

            // Which order proved the purchase. Nullable so deleting an order later
            // doesn't cascade-delete the review itself.
            $table->unsignedBigInteger('order_id')->nullable();

            $table->tinyInteger('rating')->unsigned(); // 1-5, enforced in validation + DB check below
            $table->text('review')->nullable();

            // Always true today (a review can only be created after the purchase
            // check passes), but stored so you can show a "Verified Purchase"
            // badge and keep the flag even if the order is later removed/refunded.
            $table->boolean('is_verified_purchase')->default(true);

            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');

            // One review per user per product. Remove this line if a user should
            // be able to leave a fresh review after buying the product again.
            $table->unique(['user_id', 'product_id']);
        });

        $this->addRatingCheckConstraint();
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }

    /**
     * Extra DB-level guard so `rating` can never be saved outside 1-5, even if
     * something bypasses Laravel validation. MySQL 8.0.16+ enforces CHECK
     * constraints; older MySQL/MariaDB parses but ignores them, in which case
     * this is a harmless no-op and validation in the controller is your real
     * protection.
     */
    private function addRatingCheckConstraint(): void
    {
        try {
            DB::statement(
                'ALTER TABLE reviews ADD CONSTRAINT reviews_rating_between_1_and_5 CHECK (rating BETWEEN 1 AND 5)'
            );
        } catch (\Throwable $e) {
            // Silently skip on engines that don't support CHECK constraints.
        }
    }
};