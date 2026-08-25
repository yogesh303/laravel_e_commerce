<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'stripe_key' => Setting::get('stripe_key'),
            'stripe_secret' => Setting::get('stripe_secret'),
            'razorpay_key' => Setting::get('razorpay_key'),
            'razorpay_secret' => Setting::get('razorpay_secret'),
            'mail_host' => Setting::get('mail_host'),
            'mail_port' => Setting::get('mail_port'),
            'mail_username' => Setting::get('mail_username'),
            'mail_password' => Setting::get('mail_password'),
            'mail_from_address' => Setting::get('mail_from_address'),
            'mail_from_name' => Setting::get('mail_from_name'),
        ];

        return view('settings', compact('settings'));
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
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
        ]);

        Setting::set('stripe_key', $data['stripe_key'] ?? '');
        // Only overwrite secret fields if the user actually typed something,
        // so re-saving the form doesn't wipe out an existing secret.
        if (!empty($data['stripe_secret'])) {
            Setting::set('stripe_secret', $data['stripe_secret'], encrypted: true);
        }

        Setting::set('razorpay_key', $data['razorpay_key'] ?? '');
        if (!empty($data['razorpay_secret'])) {
            Setting::set('razorpay_secret', $data['razorpay_secret'], encrypted: true);
        }

        Setting::set('mail_host', $data['mail_host'] ?? '');
        Setting::set('mail_port', $data['mail_port'] ?? '');
        Setting::set('mail_username', $data['mail_username'] ?? '');
        if (!empty($data['mail_password'])) {
            Setting::set('mail_password', $data['mail_password'], encrypted: true);
        }

        Setting::set('mail_from_address', $data['mail_from_address'] ?? '');
        Setting::set('mail_from_name', $data['mail_from_name'] ?? '');

        return back()->with('success', 'Settings updated successfully.');
    }
}