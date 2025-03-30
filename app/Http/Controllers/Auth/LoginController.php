<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class LoginController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try{
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            if (!$token = auth()->guard('api')->attempt($validated)) {
                return $this->sendError('Email atau Password Anda salah', [], 401);
            }
            $user = auth()->guard('api')->user();
            return $this->sendResponse([
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Barer Token',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ], 'Login berhasil');
        } catch(ValidationException $e) {
            return $this->sendError('Validasi gagal', $e->errors(), 422);
        } catch (AuthenticationException $e) {
            return $this->sendError('Autentikasi gagal', ['error' => $e->getMessage()], 401);
        } catch (Throwable $e) {
            return $this->sendError('Gagal login, silakan coba lagi', ['error' => $e->getMessage()], 500);
        }
    } 
}
