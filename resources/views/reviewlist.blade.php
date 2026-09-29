<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Reviews</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>
        .reviews-table-wrap {
            margin-top: var(--s5, 24px);
            overflow-x: auto;
            border: 1px solid var(--rule-strong, #e2e2e2);
            border-radius: var(--r, 12px);
        }

        table.reviews-table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--text-sm, 14px);
        }

        table.reviews-table th,
        table.reviews-table td {
            padding: 12px 16px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid var(--rule-strong, #e2e2e2);
        }

        table.reviews-table th {
            background: var(--bg, #f7f7f8);
            font-weight: 700;
            white-space: nowrap;
        }

        table.reviews-table tr:last-child td {
            border-bottom: none;
        }

        .stars-static {
            color: #f5b301;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .review-text {
            max-width: 360px;
            overflow-wrap: anywhere;
            color: var(--fg-soft, #444);
        }

        .verified-badge {
            font-size: var(--text-xs, 12px);
            color: #1a7f4e;
            background: #e6f7ee;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
            display: inline-block;
            margin-top: 4px;
        }

        .btn-delete {
            border: none;
            background: #fdecea;
            color: #c0392b;
            padding: 8px 14px;
            border-radius: var(--r, 8px);
            font-size: var(--text-xs, 12px);
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-delete:hover {
            background: #f9d6d2;
        }

        .reviews-empty {
            padding: var(--s5, 24px);
            text-align: center;
            color: var(--fg-mute, #888);
        }
    </style>
</head>
<body>
<x-layout></x-layout>

<main id="main">
<div class="container">

    @if(session('success'))
        <div class="alert alert-success" style="padding:var(--s4);border-radius:var(--r);background:#e6f7ee;color:#1a7f4e;margin-top:var(--s5)">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="padding:var(--s4);border-radius:var(--r);background:#fdecea;color:#c0392b;margin-top:var(--s5)">{{ $errors->first() }}</div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:var(--s5);flex-wrap:wrap;gap:10px;">
        <h1 style="margin:0;">All Reviews</h1>
        <span style="font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute)">
            {{ $reviews->count() }} total
        </span>
    </div>

    <div class="reviews-table-wrap">
        @if($reviews->count())
            <table class="reviews-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if($review->product)
                                    <a href="{{ url('product/' . $review->product->id) }}" style="font-weight:600;color:inherit;text-decoration:underline;">
                                        {{ $review->product->name }}
                                    </a>
                                @else
                                    <span style="color:var(--fg-mute)">Deleted product</span>
                                @endif
                            </td>

                            <td>
                                {{ $review->user->name ?? 'Deleted user' }}
                                @if($review->is_verified_purchase)
                                    <span class="verified-badge">Verified</span>
                                @endif
                            </td>

                            <td>
                                <span class="stars-static">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </td>

                            <td class="review-text">
                                {{ $review->review ?: '—' }}
                            </td>

                            <td style="white-space:nowrap;font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute)">
                                {{ $review->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <form method="POST" action="{{ route('reviews.destroy', $review->id) }}"
                                      onsubmit="return confirm('Delete this review? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="reviews-empty">No reviews have been submitted yet.</div>
        @endif
    </div>

</div>
</main>

<x-footer></x-footer>
<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>
