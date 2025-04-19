<?php

namespace App\Http\Controllers\Analyze;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\AnalisisProduk;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use App\Models\Resiko;
use App\Models\Kandungan;

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

            if (!$request->hasFile('image') && !$request->input('ingredients')) {
                return $this->sendError('Gambar atau bahan tidak ditemukan, isi salah satu', [], 422);
            }

            // Simpan gambar jika tersedia
            $fullPath = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('images');
                $fullPath = storage_path('app/' . $path);
            }

            // Kirim data ke API HuggingFace
            if ($request->hasFile('image')) {
                $response = Http::attach(
                    'image', file_get_contents($fullPath), basename($fullPath)
                )->post('https://huggingface.co/spaces/Maulidaaa/skicare_analyze', []);
            } else {
                $response = Http::asForm()->post('https://huggingface.co/spaces/Maulidaaa/skicare_analyze', [
                    'ingredients' => $request->input('ingredients')
                ]);
            }

            if (!$response->successful()) {
                return $this->sendError('Gagal mengirim data ke API lain', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            $data = $response->json();

            // Cek dan buat kategori
            $category = $request->input('product_category');
            $kategori = Kategori::firstOrCreate(['name' => $category]);

            // Simpan produk utama
            $produk = Produk::create([
                'name' => $request->input('product_name'),
                'brand' => $request->input('product_brand'),
                'kategoris_id' => $kategori->id,
            ]);

            $user = auth()->guard('api')->user();

            // Simpan analisis efek setelah penggunaan
            $analisis = $data['Predicted After Use Effects']['description'] ?? 'Tidak ada efek terdeteksi';
            AnalisisProduk::create([
                'user_id' => $user->id,
                'produk_id' => $produk->id,
                'analisis' => $analisis,
            ]);

            // Simpan kandungan & resiko
            if (!empty($data['Ingredient Analysis'])) {
                foreach ($data['Ingredient Analysis'] as $item) {
                    $resiko = Resiko::firstOrCreate(
                        ['description' => $item['Risk Description']],
                        ['level' => $item['Risk Level']]
                    );

                    Kandungan::updateOrCreate(
                        ['produk_id' => $produk->id, 'name' => $item['Ingredient Name']],
                        [
                            'fungsi' => $item['Function'],
                            'resiko_id' => $resiko->id
                        ]
                    );
                }
            }

            // Simpan rekomendasi produk
            if (!empty($data['Product Recommendations'])) {
                foreach ($data['Product Recommendations'] as $rekom) {
                    $rekomKategori = Kategori::firstOrCreate(['name' => $rekom['type']]);

                    $rekomProduk = Produk::updateOrCreate(
                        ['name' => $rekom['name']],
                        [
                            'brand' => $rekom['brand'],
                            'kategoris_id' => $rekomKategori->id,
                        ]
                    );

                    // Simpan kandungan dari ingredients (dipisah per koma)
                    $ingredients = explode(',', $rekom['ingredients']);
                    foreach ($ingredients as $ing) {
                        $namaBahan = trim($ing);
                        Kandungan::firstOrCreate([
                            'produk_id' => $rekomProduk->id,
                            'name' => $namaBahan,
                            'fungsi' => 'Unknown',
                            'resiko_id' => null
                        ]);
                    }
                }
            }

            return $this->sendResponse($data, 'Data berhasil dianalisis dan disimpan.');

        } catch (\Exception $e) {
            return $this->sendError('Terjadi kesalahan saat menganalisis produk', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
