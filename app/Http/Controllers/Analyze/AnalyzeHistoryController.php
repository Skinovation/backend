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

    public function show($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $analisis = AnalisisProduk::with(['produk.kategori'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$analisis) {
            return $this->sendError('Analisis tidak ditemukan', [], 404);
        }

        $rekomendasi = RekomendasiProduk::with(['produk.kategori'])
            ->where('analisis_produk_id', $analisis->id)
            ->get()
            ->map(function ($rekomendasi) {
                return [
                    'id' => $rekomendasi->produk->id,
                    'nama' => $rekomendasi->produk->nama,
                    'brand' => $rekomendasi->produk->brand,
                    'kategori' => $rekomendasi->produk->kategori->nama ?? null,
                ];
            });

        return $this->sendResponse([
            'analisis' => [
                'id' => $analisis->id,
                'tanggal' => $analisis->created_at->toDateTimeString(),
                'produk' => [
                    'nama' => $analisis->produk->nama,
                    'brand' => $analisis->produk->brand,
                    'kategori' => $analisis->produk->kategori->nama ?? null,
                ],
                'hasil_analisis' => $analisis->analisis,
            ],
            'rekomendasi' => $rekomendasi,
        ], 'Detail analisis berhasil diambil');
    }
}
