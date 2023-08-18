<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AutenticationController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = auth()->attempt($credentials);
        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, verifique su email o contraseña'
            ], 401);
        }
        return response()->json([
            'success' => true, 
            'access_token' => $token,
                
        ],200);
    }
}
