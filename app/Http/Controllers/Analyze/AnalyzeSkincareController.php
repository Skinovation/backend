<?php

namespace App\Http\Controllers\Analyze;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\AnalisisProduk;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
            $response = $request->hasFile('image')
                ? Http::attach('image', file_get_contents($fullPath), basename($fullPath))
                    ->post('https://maulidaaa-skincare.hf.space/analyze', [])
                : Http::asForm()->post('https://maulidaaa-skincare.hf.space/analyze', [
                    'ingredients' => $request->input('ingredients')
                ]);

            if (!$response->successful()) {
                return $this->sendError('Gagal mengirim data ke API lain', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            $data = $response->json();

            // Simpan kategori produk
            $kategori = Kategori::firstOrCreate(['nama' => $request->input('product_category')]);

            // Simpan produk utama
            $produk = Produk::create([
                'nama' => $request->input('product_name'),
                'brand' => $request->input('product_brand'),
                'kategori_id' => $kategori->id,
            ]);

            $user = auth()->guard('api')->user();

            // Simpan analisis efek
            $analisis = $data['Predicted After Use Effects']['description'] ?? 'Tidak ada efek terdeteksi';
            AnalisisProduk::create([
                'user_id' => $user->id,
                'produk_id' => $produk->id,
                'analisis' => $analisis,
            ]);

            // Simpan kandungan dan pivot-nya
            if (!empty($data['Ingredient Analysis'])) {
                foreach ($data['Ingredient Analysis'] as $item) {
                    $resiko = Resiko::firstOrCreate(
                        ['deskripsi' => $item['Risk Description']],
                        ['tingkat_resiko' => $item['Risk Level']]
                    );

                    $kandungan = Kandungan::firstOrCreate(
                        ['nama' => $item['Ingredient Name']],
                        [
                            'fungsi' => $item['Function'],
                            'kategori_id' => $kategori->id
                        ]
                    );

                    // Simpan pivot
                    DB::table('kandungan_produk')->insert([
                        'produks_id' => $produk->id,
                        'kandungan_id' => $kandungan->id
                    ]);
                }
            }

            // Simpan rekomendasi produk dan kandungannya
            if (!empty($data['Product Recommendations'])) {
                foreach ($data['Product Recommendations'] as $rekom) {
                    $rekomKategori = Kategori::firstOrCreate(['nama' => $rekom['type']]);

                    $rekomProduk = Produk::updateOrCreate(
                        ['nama' => $rekom['name']],
                        [
                            'brand' => $rekom['brand'],
                            'kategori_id' => $rekomKategori->id
                        ]
                    );

                    // Simpan hubungan rekomendasi
                    \App\Models\RekomendasiProduk::firstOrCreate([
                        'produk_id' => $produk->id,
                        'produk_alternatif_id' => $rekomProduk->id
                    ]);

                    // Simpan kandungan dari rekomendasi
                    $ingredients = explode(',', $rekom['ingredients']);
                    foreach ($ingredients as $ing) {
                        $namaBahan = trim($ing);
                        $kandungan = Kandungan::firstOrCreate(
                            ['nama' => $namaBahan],
                            ['fungsi' => 'Unknown', 'kategori_id' => $rekomKategori->id]
                        );

                        DB::table('kandungan_produk')->updateOrInsert([
                            'produks_id' => $rekomProduk->id,
                            'kandungan_id' => $kandungan->id
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
