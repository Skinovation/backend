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
use App\Models\KandunganProduk;
use Illuminate\Support\Facades\Log;

class AnalyzeSkincareController extends BaseController
{
    public function Analyze(Request $request)
    {
        try {
            Log::info('🔍 Memulai analisis produk skincare');

            $validated = $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'ingredients' => 'string|nullable',
                'product_name' => 'required|string',
                'product_brand' => 'required|string',
                'product_category' => 'required|string',
            ]);

            if (!$request->hasFile('image') && !$request->input('ingredients')) {
                return $this->sendError('Gambar atau daftar bahan harus diisi salah satu.', [], 422);
            }

            $imagePath = null;
            $imageUrl = null;

            if ($request->hasFile('image')) {
                // Simpan ke storage/app/public/images
                $path = $request->file('image')->store('images', 'public');

                // URL untuk diakses publik
                $imagePath = 'storage/' . $path;
                $imageUrl = asset($imagePath);

                Log::info('🖼️ Gambar disimpan di: ' . public_path($imagePath));
            }

            // Kirim ke HuggingFace API
            Log::info('📡 Mengirim data ke API HuggingFace...');
            $response = $request->hasFile('image')
                ? Http::timeout(60)
                    ->attach('image', file_get_contents(public_path($imagePath)), basename($imagePath))
                    ->post('https://maulidaaa-skincare.hf.space/analyze')
                : Http::timeout(60)
                    ->asForm()
                    ->post('https://maulidaaa-skincare.hf.space/analyze', [
                        'ingredients' => $request->input('ingredients')
                    ]);

            if (!$response->successful()) {
                Log::error('❌ Gagal dari API HuggingFace', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->sendError('Gagal memproses data dari API eksternal.', [], $response->status());
            }

            $resultData = $response->json();
            Log::info('✅ Data berhasil diterima dari API');

            DB::beginTransaction();

            // Simpan produk & kategori
            $kategori = Kategori::firstOrCreate(['nama' => $request->input('product_category')]);
            $produk = Produk::create([
                'nama' => $request->input('product_name'),
                'brand' => $request->input('product_brand'),
                'kategoris_id' => $kategori->id,
            ]);

            // Simpan analisis
            $user = auth()->guard('api')->user();
            $analisis = $resultData['Predicted After Use Effects']['description'] ?? 'Tidak ada efek terdeteksi';

            AnalisisProduk::create([
                'user_id' => $user->id,
                'produk_id' => $produk->id,
                'analisis' => $analisis,
            ]);

            // Simpan Kandungan dari API
            if (!empty($resultData['Ingredient Analysis'])) {
                foreach ($resultData['Ingredient Analysis'] as $item) {
                    $resiko = Resiko::firstOrCreate(
                        ['deskripsi' => $item['Risk Description']],
                        ['tingkat_resiko' => $item['Risk Level'], 'code' => $item['Restriction']]
                    );

                    $kandungan = Kandungan::firstOrCreate(
                        ['name' => $item['Ingredient Name']],
                        ['fungsi' => $item['Function'], 'resiko_id' => $resiko->id]
                    );

                    KandunganProduk::firstOrCreate([
                        'produks_id' => $produk->id,
                        'kandungans_id' => $kandungan->id
                    ]);
                }
            }

            // Cek dan tambahkan bahan baru ke database
            $inputIngredients = $request->input('ingredients');
            $ingredients = array_filter(array_map('trim', explode(',', $inputIngredients)));
            $bahanBaru = [];

            foreach ($ingredients as $namaBahan) {
                if (!Kandungan::where('name', $namaBahan)->exists()) {
                    $bahanBaru[] = $namaBahan;
                }
            }

            if (!empty($bahanBaru)) {
                $apiRes = Http::timeout(60)->post('https://maulidaaa-predict.hf.space/analyze', [
                    'ingredients' => implode(', ', $bahanBaru)
                ]);

                if ($apiRes->successful()) {
                    foreach ($apiRes->json()['Ingredient Analysis'] as $item) {
                        $resiko = Resiko::firstOrCreate(
                            ['deskripsi' => $item['Risk Description']],
                            ['tingkat_resiko' => $item['Risk Level'], 'code' => $item['Restriction']]
                        );

                        Kandungan::updateOrCreate(
                            ['name' => $item['Ingredient Name']],
                            ['fungsi' => $item['Function'], 'resiko_id' => $resiko->id]
                        );
                    }
                } else {
                    Log::warning('⚠️ API bahan prediksi gagal', ['status' => $apiRes->status()]);
                }
            }

            // Hubungkan semua kandungan ke produk
            foreach ($ingredients as $namaBahan) {
                $kandungan = Kandungan::where('name', $namaBahan)->first();
                if ($kandungan) {
                    KandunganProduk::firstOrCreate([
                        'produks_id' => $produk->id,
                        'kandungans_id' => $kandungan->id
                    ]);
                }
            }

            DB::commit();
            Log::info('💾 Semua data berhasil disimpan');

            if (isset($resultData['Predicted After Use Effects'])) {
                unset($resultData['Predicted After Use Effects']['skor']);
                unset($resultData['Predicted After Use Effects']['description']);
                
            }

            $label = $resultData['Predicted After Use Effects']['labels'];

            return $this->sendResponse([
                'produk' => [
                    'nama' => $produk->nama,
                    'brand' => $produk->brand,
                    'kategori' => $kategori->nama,
                    'image_url' => $imageUrl,
                    'deskripsi' => $analisis,
                    'label' => $label,
                ],
                'detail produk' => $resultData,
            ], 'Analisis produk berhasil dilakukan.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❗ Terjadi error saat analisis produk', ['message' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan saat memproses data.', ['error' => $e->getMessage()], 500);
        }
    }
}
