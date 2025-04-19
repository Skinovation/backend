<?php

namespace App\Http\Controllers\Analyze;

use App\Http\Controllers\BaseController;
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

            // Simpan gambar ke storage
            $path = $request->file('image')->store('images');

            return $this->sendResponse(['path' => $path], 'Gambar berhasil diunggah');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengunggah gambar', ['error' => $e->getMessage()], 500);
        }
    }
}
