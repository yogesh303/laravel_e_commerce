<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a 1-5 star review for a product — only allowed if the logged-in
     * user has an order containing that product (checked against
     * orders + order_items).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'review'     => 'nullable|string|max:2000',
        ]);

        $userId    = Auth::id();
        $productId = (int) $request->product_id;

        if (Review::userAlreadyReviewed($userId, $productId)) {
            return back()->withErrors(['error' => 'You have already reviewed this product.']);
        }

        $orderId = Review::purchasedOrderId($userId, $productId);

        if (!$orderId) {
            return back()->withErrors(['error' => 'You can only review products you have purchased.']);
        }

        Review::create([
            'product_id'            => $productId,
            'user_id'               => $userId,
            'order_id'              => $orderId,
            'rating'                => $request->rating,
            'review'                => $request->review,
            'is_verified_purchase'  => true,
        ]);

        return back()->with('success', 'Thanks for your review!');
    }

    /**
     * All reviews for a product, newest first — feed this to productdetails.blade.php.
     */
    public function forProduct(int $productId)
    {
        return Review::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->get();
    }

    /**
     * Admin "All Reviews" table (resources/views/reviewlist.blade.php).
     * Lists every review across every product with product name + reviewer.
     */
    public function index()
    {
        $reviews = Review::with(['user', 'product'])
            ->latest()
            ->get();

        return view('reviewlist', ['reviews' => $reviews]);
    }

    /**
     * Delete a single review (admin action from the reviewlist table).
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return back()->withErrors(['error' => 'Review not found.']);
        }

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}