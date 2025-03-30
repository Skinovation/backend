<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutController extends BaseController
{
    /**
     * Handle logout request.
     */
    public function __invoke(Request $request)
    {
        try {
            if (!$request->isMethod('post')) {
                return $this->sendError('Metode tidak diizinkan', [], 405);
            }

            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

            if ($removeToken) {
                return $this->sendResponse([], 'Logout berhasil');
            } else {
                return $this->sendError('Logout gagal', [], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Gagal logout, silakan coba lagi', ['error' => $e->getMessage()], 500);
        } 
    }
}
