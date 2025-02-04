<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
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
        return response()->json(['user' => $user, 'token' => $token, 'status' => 'success']);
    }
    // Connexion
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user = JWTAuth::user();
        return response()->json(['user' => $user, 'token' => $token, 'status' => 'success', 'role' => $user['role'],]);
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
    public function deleteUser($id)
    {
        try {
            $silo = User::find($id);
            $silo->delete();
            return response()->json(['message' => 'Utilisateur supprimé avec succès !'], 200,);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
    // update role par admin
    public function updateRoleByEmail(Request $request)
    {

        $email = $request->input('email');
        $newRole = $request->input('role');

        $user = User::where('email', $email)->firstOrFail();

        $user->role = $newRole;
        $user->save();

        return response()->json(['message' => 'Le rôle a été mis à jour avec succès']);
    }
}
