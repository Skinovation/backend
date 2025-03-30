<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = bcrypt($validated['password']);
            $user = \App\Models\User::create($validated);
            $credentials = $request->only('email', 'password');
            if(!$token = auth()->guard('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau Password Anda salah'
                ], 401);
            }
            $user = auth()->guard('api')->user();
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'JWT',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi gagal: ' . $e->getMessage(),
            ], 401);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
