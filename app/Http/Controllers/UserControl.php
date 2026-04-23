<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\products;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserControl extends Controller
{
    //
    public function login_user(Request $data){
        $validated = $data->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);
        $email = User::where('email', $validated['email'])->first();

        if(!$email){
            return back()->withErrors(['email' => 'Email not found']);
        }
        if(!Hash::check($validated['password'], $email->password)){
            return back()->withErrors(['password' => 'Password incorrect']);
        }

        Auth::login($email);

        return redirect('dashboard');

    }
    public function signup_user(Request $data){

        $validated = $data->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
         $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::login($user);

        if($user){
            return redirect('products');
        }
    }
    public function logout()
    {
        Auth::logout(); // remove session

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
            'totalPrice' => $totalPrice
        ]);
    }
}
