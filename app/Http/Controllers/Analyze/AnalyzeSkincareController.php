<?php

namespace App\Http\Controllers\Analyze;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Faker\Provider\Base;
use Illuminate\Http\Request;

class AnalyzeSkincareController extends BaseController
{
    public function Analyze(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'ingredients' => 'string',
                'product_name' => 'required|string',
                'product_brand' => 'required|string',
                'product_category' => 'required|string',
            ]);

            if(!$request->hasFile('image')&& !$request->input('ingredients')) {
                return $this->sendError('Gambar atau bahan tidak ditemukan, isi salah satu', [], 422);
            }

            // Simpan gambar ke storage dan ambil path lengkap
            $path = $request->file('image')->store('images');
            $fullPath = storage_path('app/' . $path);

            if(!$request->input('ingredients')) {
                // Kirim gambar ke API lain
                $response = Http::attach(
                    'image', file_get_contents($fullPath), basename($fullPath)
                )->post('https://huggingface.co/spaces/Maulidaaa/skicare_analyze', [
                ]);

                if ($response->successful()) {
                    return $this->sendResponse($response->json(), 'Data berhasil dikirim ke API lain');
                } else {
                    return $this->sendError('Gagal mengirim data ke API lain', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } else {
                // Kirim gambar ke API lain
                $response = Http::attach(
                    'ingredients', $request->input('ingredients'), basename($fullPath)
                )->post('https://huggingface.co/spaces/Maulidaaa/skicare_analyze', [
                ]);

                if ($response->successful()) {
                    return $this->sendResponse($response->json(), 'Data berhasil dikirim ke API lain');
                } else {
                    return $this->sendError('Gagal mengirim data ke API lain', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengunggah gambar', ['error' => $e->getMessage()], 500);
        }
    }
}
