<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

/**
 * Class RegisterController
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends BaseController
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
            return $this->sendResponse([
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'JWT',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ], 'Registrasi berhasil');
        } catch (ValidationException $e) {
            return $this->sendError('Validasi gagal', $e->errors(), 422);
        }
        catch (AuthenticationException $e) {
            return $this->sendError('Autentikasi gagal', ['error' => $e->getMessage()], 401);
        }
        catch (Throwable $e) {
            return $this->sendError('Gagal mendaftar, silakan coba lagi', ['error' => $e->getMessage()], 500);
        }
    }
}
