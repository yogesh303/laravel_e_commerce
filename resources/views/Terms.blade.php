<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms &amp; Conditions</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">

    <section class="page-head">
      <div class="container">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> <span class="sep">›</span> <span>Terms &amp; conditions</span></div>
        <h1>Terms &amp; Conditions</h1>
        <p>Last updated: {{ now()->format('d F Y') }}</p>
      </div>
    </section>

    <section>
      <div class="container">
        <div style="color: var(--fg-soft); line-height: 1.75;">

          <p style="margin-bottom: var(--s5, 20px);">
            These terms govern your use of this website and any orders placed with E-Commerce Pvt.
            Ltd. ("we", "us", "our"). By placing an order or using this site, you agree to these terms.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">1. Orders and pricing</h2>
          <p style="margin-bottom: var(--s3, 12px);">
            Product prices, including any per-piece pricing tiers and option surcharges, are shown at
            the time of purchase and may change without notice. Placing an order is an offer to buy,
            which we may accept or decline — for example, if a product is unavailable or details
            provided (such as sizing or customization) are invalid.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">2. Customized products</h2>
          <p style="margin-bottom: var(--s3, 12px);">
            For customized or personalized items (including uploaded logos, artwork, or custom
            images), you confirm that you own or have the right to use any content you submit, and
            that it does not infringe on any third party's rights. We review customization details
            before production; please check your options, sizes, and files carefully — once production
            begins, customized orders generally cannot be changed or cancelled.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">3. Payment</h2>
          <p>
            Payments are processed securely through our third-party payment partners (Stripe and
            Razorpay). We do not store your full card details. Your order is confirmed only once
            payment has been successfully verified.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">4. Shipping and delivery</h2>
          <p>
            We aim to dispatch and deliver orders within the timelines communicated at checkout or by
            our support team. Delivery timelines are estimates and may vary due to factors outside our
            control, such as courier delays.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">5. Returns and refunds</h2>
          <p>
            Non-customized products may be eligible for return within a reasonable period of delivery
            if defective or not as described — contact us to arrange this. Customized or personalized
            products are generally not eligible for return or refund, except where the item is
            defective or does not match the confirmed order details.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">6. Invoicing and taxes</h2>
          <p>
            Applicable taxes (including GST) are calculated and shown on your invoice. Business
            accounts providing GST details are responsible for ensuring the information is accurate.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">7. Account responsibility</h2>
          <p>
            If you create an account, you're responsible for keeping your login details secure and
            for activity that happens under your account.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">8. Limitation of liability</h2>
          <p>
            To the extent permitted by law, we are not liable for indirect, incidental, or
            consequential damages arising from your use of this site or our products, beyond the
            amount you paid for the relevant order.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">9. Changes to these terms</h2>
          <p>
            We may update these terms from time to time. Continued use of the site after changes are
            posted means you accept the updated terms.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">10. Governing law</h2>
          <p>
            These terms are governed by the laws of India, and any disputes will be subject to the
            exclusive jurisdiction of the courts in Ahmedabad, Gujarat.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">11. Contact us</h2>
          <p>
            Questions about these terms can be sent to
            <a href="mailto:yogeshkanzariya5@gmail.com" style="color: var(--indigo);">yogeshkanzariya5@gmail.com</a>
            or via our <a href="{{ route('contact') }}" style="color: var(--indigo);">contact page</a>.
          </p>

        </div>
      </div>
    </section>

  </main>

<x-footer></x-footer>

<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>