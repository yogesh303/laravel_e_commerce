<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Validate the form and email the support inbox.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'topic'   => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Change this to wherever support mail should land — env('MAIL_SUPPORT_TO')
        // is a good option if you want it configurable per environment.
        $to = config('mail.support_to', 'support@mahiprint.com');

        Mail::to($to)->send(new ContactMail($validated));

        return redirect()
            ->route('contact')
            ->with('success', 'Thanks — your message has been sent. We will get back to you within 24 hours.');
    }
}