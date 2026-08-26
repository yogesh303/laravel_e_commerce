{{-- resources/views/components/footer.blade.php --}}

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="mark"><span class="brand-mark">🛒</span> E-Commerce</div>
                <p>Quality products, fast shipping, and support you can count on. Shop the full catalog anytime.</p>
                <div class="socials" style="margin-top:var(--s4)">
                    <a href="#" aria-label="Twitter"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.8a8.5 8.5 0 0 1-2.4.7 4.2 4.2 0 0 0 1.8-2.3 8.4 8.4 0 0 1-2.6 1 4.2 4.2 0 0 0-7.2 3.8A11.9 11.9 0 0 1 3 4.8a4.2 4.2 0 0 0 1.3 5.6 4.2 4.2 0 0 1-1.9-.5v.1a4.2 4.2 0 0 0 3.4 4.1 4.2 4.2 0 0 1-1.9.1 4.2 4.2 0 0 0 3.9 2.9A8.4 8.4 0 0 1 2 18.7 11.9 11.9 0 0 0 8.5 21c7.7 0 11.9-6.4 11.9-11.9v-.5A8.5 8.5 0 0 0 22 5.8z"/></svg></a>
                    <a href="#" aria-label="LinkedIn"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM8 18H5v-7h3v7zM6.5 9.7a1.7 1.7 0 1 1 0-3.4 1.7 1.7 0 0 1 0 3.4zM18 18h-3v-4c0-1-.4-1.6-1.4-1.6S12 13 12 14v4H9v-7h3v1c.5-.8 1.4-1.2 2.4-1.2 2 0 3.6 1.4 3.6 4V18z"/></svg></a>
                    <a href="#" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
                    <a href="#" aria-label="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23 7s-.2-1.5-.9-2.2c-.8-.9-1.8-.9-2.2-1C16.6 3.5 12 3.5 12 3.5s-4.6 0-7.9.3c-.4 0-1.4 0-2.2 1C1.2 5.5 1 7 1 7s-.2 1.7-.2 3.5v1.6c0 1.7.2 3.5.2 3.5s.2 1.5.9 2.2c.8.9 1.9.8 2.4.9 1.7.2 7.7.3 7.7.3s4.6 0 7.9-.3c.4 0 1.4 0 2.2-1 .7-.7.9-2.2.9-2.2s.2-1.7.2-3.5V10.5c0-1.7-.2-3.5-.2-3.5zM9.7 14.5V8.4l6 3-6 3.1z"/></svg></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Shop</h4>
                <ul>
                    <li><a href="{{ url('products') }}">All Products</a></li>
                    <li><a href="{{ url('cart') }}">My Cart</a></li>
                    @auth
                        <li><a href="{{ url('orders') }}">My Orders</a></li>
                    @endauth
                </ul>
            </div>

            <div class="footer-col">
                <h4>Let us help you</h4>
                <ul>
                    @auth
                        <li><a href="{{ url('dashboard') }}">My account</a></li>
                        <li><a href="{{ url('orders') }}">My orders</a></li>
                    @else
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li><a href="{{ url('/signup') }}">Sign up</a></li>
                    @endauth
                    <li><a href="#">Shipping policy</a></li>
                    <li><a href="#">Help centre</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Get to know us</h4>
                <ul>
                    <li><a href="#">About us</a></li>
                    <li><a href="#">Contact</a></li>
                    @guest
                        <li><a href="{{ url('/login') }}">Login / Register</a></li>
                    @endguest
                    <li><a href="#">Terms &amp; conditions</a></li>
                    <li><a href="#">Privacy policy</a></li>
                    <li><a href="#">FAQs</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© {{ date('Y') }} E-Commerce · All rights reserved</span>
            <span>Built with Laravel</span>
        </div>
    </div>
</footer>