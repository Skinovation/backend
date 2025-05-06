<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komentar;
use App\Models\Postingan;

class KomenController extends BaseController
{
    public function index($id){
        $postingan = Postingan::find($id)->with('user')->first();
        $komentar = Komentar::where('posting_id', $id)->with('user')->get();
        if(!$postingan){
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        if($komentar->isEmpty()){
            return response()->json([
                'message' => 'Tidak ada komentar',
            ], 404);
        }
        return $this->sendResponse([
            'data' => [
                'postingan' => $postingan,
                'komentar' => $komentar,
            'total' => Komentar::where('posting_id', $id)->count(),
            ],
        ], 'Berhasil mendapatkan data');
        
    }

    public function store(Request $request, $id){
        $request->validate([
            'komentar' => 'required|string|max:255',
        ]);

        $komentar = new Komentar();
        $komentar->user_id = auth()->guard('api')->user()->id;
        $komentar->posting_id = $id;
        $komentar->komentar = $request->komentar;

        if($komentar->save()){
            return $this->sendResponse([
                'data' => $komentar,
            ], 'Berhasil menambahkan data');
        } else {
            return $this->sendError('Gagal menambahkan data', [], 500);
        }
    }
    public function edit($id){
        $komentar = Komentar::find($id);
        if(!$komentar){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }
        return $this->sendResponse([
            'data' => $komentar,
        ], 'Berhasil mendapatkan data');
    }
    public function update(Request $request, $id){
        $komentar = Komentar::find($id);
        if(!$komentar){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }
        $request->validate([
            'komentar' => 'required|string|max:255',
        ]);
        $komentar->komentar = $request->komentar;
        if($komentar->save()){
            return $this->sendResponse([
                'data' => $komentar,
            ], 'Berhasil mengupdate data');
        } else {
            return $this->sendError('Gagal mengupdate data', [], 500);
        }
    }
    public function destroy($id){
        $komentar = Komentar::find($id);
        if(!$komentar){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }
        if($komentar->delete()){
            return $this->sendResponse([
                'data' => $komentar,
            ], 'Berhasil menghapus data');
        } else {
            return $this->sendError('Gagal menghapus data', [], 500);
        }
    }
}
