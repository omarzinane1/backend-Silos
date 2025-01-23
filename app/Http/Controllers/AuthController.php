<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(['user' => $user, 'token' => $token,'status'=>'success']);
    }
    // Connexion
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user = JWTAuth::user();
        return response()->json(['user' => $user,'token' => $token,'status'=>'success','role' => $user['role'],]);
    }
    // Déconnexion
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken()); // Invalide le token actuel
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $exception) {
            return response()->json(['error' => 'Could not log out'], 500);
        }
    }

    // l'utilisateur authentifié
    public function me()
    {
        return response()->json(JWTAuth::user());
    }
}
