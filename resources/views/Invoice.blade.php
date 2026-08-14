<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
        }

        .invoice-box {
            max-width: 900px;
            margin: 30px auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        .invoice-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        table.invoice-table th {
            background: #212529;
            color: #fff;
            font-size: 14px;
        }

        table.invoice-table td,
        table.invoice-table th {
            vertical-align: middle;
            font-size: 14px;
        }

        .totals-table td {
            padding: 6px 10px;
        }

        .no-print {
            /* visible on screen, hidden on print */
        }

        @media print {
            body {
                background: #fff;
            }

            .invoice-box {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<div class="invoice-box">

    {{-- Action buttons (hidden when printing) --}}
    <div class="d-flex justify-content-between mb-4 no-print">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Back</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Save as PDF</button>
    </div>

    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-6">
            <div class="invoice-title">TAX INVOICE</div>
            <p class="mb-0 mt-2">
                <strong>{{ config('app.name', 'Your Company Name') }}</strong><br>
                {{-- Replace with your real business details --}}
                Your Company Address Line 1<br>
                City, State - Pincode<br>
                GSTIN: 24XXXXXXXXXXXZX
            </p>
        </div>
        <div class="col-6 text-end">
            <p class="mb-1"><strong>Invoice No:</strong> {{ $invoiceNo }}</p>
            <p class="mb-1"><strong>Order Date:</strong> {{ $order->created_at->format('d-m-Y') }}</p>
            <p class="mb-1"><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        </div>
    </div>

    {{-- Bill to / Ship to --}}
    <div class="row mb-4">
        <div class="col-6">
            <h6 class="text-uppercase text-muted">Bill To</h6>
            <p class="mb-1"><strong>{{ $order->user->name }}</strong></p>
            <p class="mb-0">{{ $order->user->email }}</p>
        </div>
        <div class="col-6">
            <h6 class="text-uppercase text-muted">Ship To</h6>
            @if($order->shipping_name)
                <p class="mb-1">{{ $order->shipping_name }} | {{ $order->shipping_phone }}</p>
                @if($order->shipping_gst_no)
                    <p class="mb-1"><strong>Customer GSTIN:</strong> {{ $order->shipping_gst_no }}</p>
                @endif
                @if($order->shipping_company)
                    <p class="mb-1"><strong>Company:</strong> {{ $order->shipping_company }}</p>
                @endif
                <p class="mb-1">
                    {{ $order->shipping_address_line1 }}
                    @if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif
                </p>
                <p class="mb-0">
                    {{ $order->shipping_city }}, {{ $order->shipping_state }}
                    - {{ $order->shipping_pincode }}, {{ $order->shipping_country }}
                </p>
            @else
                <p class="text-muted mb-0">No shipping address on file.</p>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <table class="table invoice-table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Rate (incl. GST)</th>
                <th class="text-end">Taxable Value</th>
                <th class="text-end">GST @ {{ $gstRate }}%</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">₹ {{ number_format($item->price, 2) }}</td>
                    <td class="text-end">₹ {{ number_format($item->taxable, 2) }}</td>
                    <td class="text-end">₹ {{ number_format($item->gst_amount, 2) }}</td>
                    <td class="text-end">₹ {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="row">
        <div class="col-md-7"></div>
        <div class="col-md-5">
            <table class="table totals-table mb-0">
                <tr>
                    <td>Taxable Amount</td>
                    <td class="text-end">₹ {{ number_format($taxableTotal, 2) }}</td>
                </tr>
                <tr>
                    <td>CGST @ {{ $gstRate / 2 }}%</td>
                    <td class="text-end">₹ {{ number_format($cgstTotal, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST @ {{ $gstRate / 2 }}%</td>
                    <td class="text-end">₹ {{ number_format($sgstTotal, 2) }}</td>
                </tr>
                <tr class="border-top">
                    <td><strong>Total GST</strong></td>
                    <td class="text-end"><strong>₹ {{ number_format($gstTotal, 2) }}</strong></td>
                </tr>
                <tr class="border-top">
                    <td><h5 class="mb-0">Grand Total</h5></td>
                    <td class="text-end"><h5 class="mb-0 text-success">₹ {{ number_format($grandTotal, 2) }}</h5></td>
                </tr>
            </table>
        </div>
    </div>

    <p class="text-muted mt-4 mb-0" style="font-size:12px;">
        This is a system-generated invoice. Prices shown are inclusive of {{ $gstRate }}% GST.
    </p>

</div>

</body>
</html>