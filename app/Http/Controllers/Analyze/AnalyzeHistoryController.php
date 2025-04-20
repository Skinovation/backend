<?php

namespace App\Http\Controllers\Analyze;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AnalisisProduk;
use App\Models\Produk;
use App\Models\RekomendasiProduk;

class AnalyzeHistoryController extends BaseController
{
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $histories = AnalisisProduk::with(['produk.kategori'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($analisis) {
                return [
                    'id' => $analisis->id,
                    'tanggal' => $analisis->created_at->toDateTimeString(),
                    'produk' => [
                        'nama' => $analisis->produk->nama,
                        'brand' => $analisis->produk->brand,
                        'kategori' => $analisis->produk->kategori->nama ?? null,
                    ],
                    'hasil_analisis' => $analisis->analisis,
                ];
            });

        return $this->sendResponse($histories, 'Riwayat analisis berhasil diambil');
    }
}
