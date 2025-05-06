<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postingan;
use App\Models\Komentar;
use Illuminate\Support\Facades\Auth;

class SosialMediaController extends BaseController
{
    public function index(){
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $postingan = Postingan::with('user')->offset($offset)->limit($limit)->get();
        $total = Postingan::count();
        return $this->sendResponse([
            'data' => $postingan,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ], 'Berhasil mendapatkan data');
    }

    public function store(Request $request){
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $postingan = new Postingan();
        $postingan->user_id =auth()->guard('api')->user()->id;
        $postingan->judul = $request->judul;
        $postingan->isi = $request->isi;

        if($request->hasFile('gambar')){
            $file = $request->file('gambar');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $postingan->gambar = $filename;
        }

        if($postingan->save()){
            return $this->sendResponse([
                'data' => $postingan,
            ], 'Berhasil menambahkan data');
        } else {
            return $this->sendError('Gagal menambahkan data', [], 500);
        }
    }

    public function edit($id){
        $postingan = Postingan::find($id);
        if(!$postingan){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }
        return $this->sendResponse([
            'data' => $postingan,
        ], 'Berhasil mendapatkan data');
    }

    public function update(Request $request, $id){
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $postingan = Postingan::find($id);
        if(!$postingan){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }

        $postingan->judul = $request->judul;
        $postingan->isi = $request->isi;

        if($request->hasFile('gambar')){
            $file = $request->file('gambar');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $postingan->gambar = $filename;
        }

        if($postingan->save()){
            return $this->sendResponse([
                'data' => $postingan,
            ], 'Berhasil memperbarui data');
        } else {
            return $this->sendError('Gagal memperbarui data', [], 500);
        }
    }

    public function destroy($id){
        $postingan = Postingan::find($id);
        if(!$postingan){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }

        if($postingan->delete()){
            return $this->sendResponse([], 'Berhasil menghapus data');
        } else {
            return $this->sendError('Gagal menghapus data', [], 500);
        }
    }
    public function comment(Request $request, $id){
        $request->validate([
            'komentar' => 'required|string',
        ]);

        $postingan = Postingan::find($id);
        if(!$postingan){
            return $this->sendError('Data tidak ditemukan', [], 404);
        }

        $komentar = new Komentar();
        $komentar->user_id = auth()->guard('api')->user()->id;
        $komentar->postingan_id = $id;
        $komentar->komentar = $request->komentar;

        if($komentar->save()){
            return $this->sendResponse([
                'data' => $komentar,
            ], 'Berhasil menambahkan komentar');
        } else {
            return $this->sendError('Gagal menambahkan komentar', [], 500);
        }
    }
}
