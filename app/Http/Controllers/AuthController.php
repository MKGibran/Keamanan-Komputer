<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Jika otentikasi berhasil, buat token API
            $token = Auth::user()->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token
            ], 200);
        }

        return response()->json([
            'message' => 'Login gagal',
            'error' => 'Kredensial tidak valid'
        ], 401);
    }
}
