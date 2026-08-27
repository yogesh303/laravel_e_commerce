<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Help &amp; Contact</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">

    <section class="page-head">
      <div class="container">
        <div class="crumbs"><a href="{{ url('/') }}">Home</a> <span class="sep">›</span> <span>Contact &amp; support</span></div>
        <h1>Talk to us — we're happy to help.</h1>
        <p>Reach our team by email, phone, or the form below — we typically reply within 24 hours.</p>
      </div>
    </section>

    <section>
      <div class="container">

        @if(session('success'))
          <div class="alert alert-success" style="margin-bottom: var(--s5);">{{ session('success') }}</div>
        @endif

        <div class="contact-grid">

          <div class="contact-info">
            <div class="info-block">
              <div class="ic">✉</div>
              <div>
                <div class="label">EMAIL US</div>
                <div class="value"><a href="mailto:support@ecommerce.com">support@ecommerce.com</a></div>
              </div>
            </div>
            <div class="info-block">
              <div class="ic">☏</div>
              <div>
                <div class="label">CALL US</div>
                <div class="value"><a href="tel:+911234567890">+91 12345 67890</a></div>
              </div>
            </div>
            <div class="info-block">
              <div class="ic">⌖</div>
              <div>
                <div class="label">VISIT US</div>
                <div class="value">
                  123 MG Road, 2nd Floor<br />
                  Ahmedabad, Gujarat 380001<br />
                  India
                </div>
              </div>
            </div>
            <div class="info-block">
              <div class="ic">⏱</div>
              <div>
                <div class="label">SUPPORT HOURS</div>
                <div class="value">Mon — Sat · 10:00 — 19:00 IST</div>
              </div>
            </div>

            <div
              style="margin-top: var(--s7); padding: var(--s6); background: linear-gradient(135deg, var(--indigo), var(--card-purple)); color: var(--paper); border-radius: var(--r-lg); position: relative; overflow: hidden">
              <div
                style="position: absolute; inset: 0; background-image: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.18) 0, transparent 40%); pointer-events: none">
              </div>
              <div style="position: relative">
                <h3 style="color: var(--paper); font-size: var(--text-xl); margin-bottom: var(--s3)">Looking for bulk
                  or trade pricing?</h3>
                <p
                  style="color: rgba(255,255,255,0.85); font-size: var(--text-sm); line-height: 1.6; margin-bottom: var(--s4)">
                  Email us directly and a member of our team will follow up with a quote.</p>
                <a href="mailto:support@ecommerce.com" class="btn btn--paper">Email us →</a>
              </div>
            </div>
          </div>

          <form class="contact-form" method="POST" action="{{ route('contact.send') }}">
            @csrf

            <h2 style="font-size: var(--text-xl); margin-bottom: var(--s2)">Send us a message</h2>
            <p style="color: var(--fg-soft); font-size: var(--text-sm); margin-bottom: var(--s5)">
              A real person will pick this up — no bots, no templated replies.</p>

            @if($errors->any())
              <div class="alert alert-error" style="margin-bottom: var(--s4);">
                <ul style="margin:0; padding-left: 18px;">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="field">
              <label for="c-name">Your name</label>
              <input id="c-name" name="name" type="text" required value="{{ old('name') }}" placeholder="Jane Doe" />
            </div>

            <div class="field">
              <label for="c-email">Email</label>
              <input id="c-email" name="email" type="email" required value="{{ old('email') }}" placeholder="you@example.com" />
            </div>

            <div class="field">
              <label for="c-phone">Phone (optional)</label>
              <input id="c-phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+91 12345 67890" />
            </div>

            <div class="field">
              <label for="c-topic">What is this about?</label>
              <select id="c-topic" name="topic">
                <option>Order — tracking, change, or cancellation</option>
                <option>Returns or refunds</option>
                <option>Product issue</option>
                <option>Bulk / trade inquiry</option>
                <option>Something else</option>
              </select>
            </div>

            <div class="field">
              <label for="c-msg">Your message</label>
              <textarea id="c-msg" name="message" required placeholder="Tell us what's going on.">{{ old('message') }}</textarea>
            </div>

            <button class="btn btn--indigo btn--block" type="submit"
              style="padding: 16px; font-size: var(--text-base); margin-top: var(--s2)">Send message →</button>

            <p
              style="font-family: var(--ff-mono); font-size: 11px; color: var(--fg-mute); margin-top: var(--s4); text-align: center; line-height: 1.6">
              By submitting, you agree to our <a href="#" style="color: var(--indigo)">privacy policy</a>. We never
              sell your data.</p>
          </form>

        </div>
      </div>
    </section>

  </main>

<x-footer></x-footer>

<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>