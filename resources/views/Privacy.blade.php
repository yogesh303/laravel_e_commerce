<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Privacy Policy</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">

    <section class="page-head">
      <div class="container">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> <span class="sep">›</span> <span>Privacy policy</span></div>
        <h1>Privacy Policy</h1>
        <p>Last updated: {{ now()->format('d F Y') }}</p>
      </div>
    </section>

    <section>
      <div class="container">
        <div style="color: var(--fg-soft); line-height: 1.75;">

          <p style="margin-bottom: var(--s5, 20px);">
            E-Commerce Pvt. Ltd. ("we", "us", "our") operates this website. This policy explains what
            information we collect, how we use it, and the choices you have. By using this site, you
            agree to the practices described below.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">1. Information we collect</h2>
          <p style="margin-bottom: var(--s3, 12px);">We collect information you provide directly to us, including:</p>
          <ul style="margin: 0 0 var(--s3, 12px) 20px;">
            <li>Name, email address, phone number, and shipping address when you place an order or contact us</li>
            <li>Payment details, processed securely by our payment partners (Stripe / Razorpay) — we do not store card numbers</li>
            <li>Order details such as product selections, customization options, sizes, and any files or images you upload</li>
            <li>GST or company details, for business accounts</li>
          </ul>
          <p>
            We also automatically collect limited technical information, such as browser type, IP
            address, and pages visited, to help us maintain and improve the site.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">2. How we use your information</h2>
          <ul style="margin: 0 0 var(--s3, 12px) 20px;">
            <li>To process and fulfil your orders, including customization and shipping</li>
            <li>To communicate with you about your order, account, or support requests</li>
            <li>To generate invoices and comply with tax and accounting obligations</li>
            <li>To improve our products, website, and customer experience</li>
          </ul>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">3. Sharing your information</h2>
          <p style="margin-bottom: var(--s3, 12px);">
            We do not sell your personal information. We share information only with:
          </p>
          <ul style="margin: 0 0 var(--s3, 12px) 20px;">
            <li>Payment processors (Stripe, Razorpay) to complete transactions</li>
            <li>Shipping and logistics partners, to deliver your order</li>
            <li>Service providers who help us operate the site (e.g. email delivery), under confidentiality obligations</li>
            <li>Authorities, where required by law</li>
          </ul>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">4. Files and images you upload</h2>
          <p>
            Custom artwork, logos, and attachments you upload for personalized products are stored
            only to fulfil your order, and are used solely for producing your customized item.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">5. Data retention</h2>
          <p>
            We retain order and account information for as long as needed to provide our services and
            meet legal, accounting, or reporting obligations, after which it is deleted or anonymized.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">6. Your choices</h2>
          <p>
            You can request access to, correction of, or deletion of your personal data by contacting
            us at the details below. Some information may need to be retained for legal or accounting
            reasons even after a deletion request.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">7. Cookies</h2>
          <p>
            We use cookies and similar technologies to keep you signed in, remember your cart, and
            understand how the site is used. You can control cookies through your browser settings.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">8. Changes to this policy</h2>
          <p>
            We may update this policy from time to time. Changes will be posted on this page with an
            updated "last updated" date.
          </p>

          <h2 style="font-size: var(--text-lg); margin: var(--s6, 32px) 0 var(--s3, 12px);">9. Contact us</h2>
          <p>
            If you have questions about this policy or how your data is handled, contact us at
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