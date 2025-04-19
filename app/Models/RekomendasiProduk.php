<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekomendasiProduk extends Model
{
    protected $table = 'rekomendasi_produk';

    protected $fillable = [

        'produk_id',
        'produk_alternatif_id',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function analisisProduk()
    {
        return $this->belongsTo(AnalisisProduk::class, 'analisis_produk_id');
    }
}
