<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

class UserControl extends Controller
{
    public function login_user(Request $data)
    {
        $validated = $data->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }
        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Password incorrect']);
        }

        Auth::login($user);

        return redirect('dashboard');
    }

    /**
     * Generate and "send" an OTP for the given mobile number.
     * Stores the OTP + expiry in session keyed to the mobile number.
     */
    public function send_otp(Request $data)
    {
        $validated = $data->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $validated['email'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered.',
            ]);
        }

        $otp = random_int(100000, 999999);

        Session::put('signup_otp', [
            'email' => $validated['email'],
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($validated['email'])->send(new OtpMail($otp));

        return response()->json(['success' => true]);
    }

    public function signup_user(Request $data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:personal,business',
            'mobile_number' => 'required|string|max:20|unique:users',
            'otp' => 'required|digits:6',
        ];

        if ($data->input('account_type') === 'business') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['gst_no'] = 'required|string|max:20';
            $rules['industry'] = 'required|string|max:255';
        }

        $validated = $data->validate($rules);

        $otpData = Session::get('signup_otp');

        if (
            !$otpData ||
            $otpData['email'] !== $validated['email'] ||
            (string) $otpData['otp'] !== (string) $validated['otp'] ||
            now()->greaterThan($otpData['expires_at'])
        ) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'account_type' => $validated['account_type'],
            'company_name' => $validated['company_name'] ?? null,
            'gst_no' => $validated['gst_no'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'mobile_number' => $validated['mobile_number'],
            'mobile_verified_at' => now(),
        ]);

        Session::forget('signup_otp');

        Auth::login($user);

        return redirect('products');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function dashboard()
    {
        $totalProducts = Products::count();
        $totalOrders = Order::count();
        $totalPrice = Order::totalRevenue();

        return view('dashboard', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalPrice' => $totalPrice,
        ]);
    }
    public function update(Request $request)
    {
        $data = $request->validate([
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
        ]);

        Setting::set('stripe_key', $data['stripe_key'] ?? '');
        Setting::set('stripe_secret', $data['stripe_secret'] ?? '', encrypted: true);
        Setting::set('razorpay_key', $data['razorpay_key'] ?? '');
        Setting::set('razorpay_secret', $data['razorpay_secret'] ?? '', encrypted: true);
        Setting::set('mail_host', $data['mail_host'] ?? '');
        Setting::set('mail_port', $data['mail_port'] ?? '');
        Setting::set('mail_username', $data['mail_username'] ?? '');
        Setting::set('mail_password', $data['mail_password'] ?? '', encrypted: true);

        return back()->with('success', 'Settings updated.');
    }
}