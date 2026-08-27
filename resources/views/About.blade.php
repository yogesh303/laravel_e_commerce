<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">

    <section class="page-head">
      <div class="container">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> <span class="sep">›</span> <span>About us</span></div>
        <h1>Built for people who need custom orders done right.</h1>
        <p>A quick look at who we are, what we do, and why customers keep coming back.</p>
      </div>
    </section>

    <section>
      <div class="container">

        <div style="margin-bottom: var(--s7, 40px);">
          <h2 style="font-size: var(--text-xl); margin-bottom: var(--s3, 12px);">Our story</h2>
          <p style="color: var(--fg-soft); line-height: 1.7; margin-bottom: var(--s3, 12px);">
            E-Commerce Pvt. Ltd. started with a simple goal: make it easy for anyone — from
            individuals to businesses — to order customized products without the usual back-and-forth.
            Whether it's a single personalised gift or a bulk order for a company event, we handle it
            with the same care and attention to detail.
          </p>
          <p style="color: var(--fg-soft); line-height: 1.7;">
            We're based in Ahmedabad, Gujarat, and ship across India, working closely with every
            customer to get sizing, options, and customization exactly right before anything goes
            into production.
          </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--s5, 20px); margin-bottom: var(--s7, 40px);">

          <div style="padding: var(--s5, 20px); border: 1px solid var(--rule, rgba(0,0,0,.08)); border-radius: var(--r, 12px);">
            <h3 style="font-size: var(--text-md); margin-bottom: var(--s2, 8px);">What we do</h3>
            <p style="color: var(--fg-soft); font-size: var(--text-sm); line-height: 1.6;">
              Customizable products with per-piece pricing tiers, size breakdowns for apparel, logo
              and artwork uploads, and order remarks — all managed from a single cart.
            </p>
          </div>

          <div style="padding: var(--s5, 20px); border: 1px solid var(--rule, rgba(0,0,0,.08)); border-radius: var(--r, 12px);">
            <h3 style="font-size: var(--text-md); margin-bottom: var(--s2, 8px);">Who we serve</h3>
            <p style="color: var(--fg-soft); font-size: var(--text-sm); line-height: 1.6;">
              Individuals ordering one-off customized items, and businesses placing bulk orders with
              GST-compliant invoicing and dedicated support.
            </p>
          </div>

          <div style="padding: var(--s5, 20px); border: 1px solid var(--rule, rgba(0,0,0,.08)); border-radius: var(--r, 12px);">
            <h3 style="font-size: var(--text-md); margin-bottom: var(--s2, 8px);">How we work</h3>
            <p style="color: var(--fg-soft); font-size: var(--text-sm); line-height: 1.6;">
              Every customized order is reviewed before production, and our support team is on hand
              for questions about sizing, files, or delivery timelines.
            </p>
          </div>

        </div>

        <div
          style="padding: var(--s6, 32px); background: linear-gradient(135deg, var(--indigo), var(--card-purple)); color: var(--paper); border-radius: var(--r-lg, 16px); text-align: center;">
          <h3 style="color: var(--paper); font-size: var(--text-xl); margin-bottom: var(--s3, 12px);">Have a question before you order?</h3>
          <p style="color: rgba(255,255,255,0.85); font-size: var(--text-sm); margin-bottom: var(--s4, 16px);">
            We're happy to help you figure out sizing, quantities, or customization options.
          </p>
          <a href="{{ route('contact') }}" class="btn btn--paper">Get in touch →</a>
        </div>

      </div>
    </section>

  </main>

<x-footer></x-footer>

<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>