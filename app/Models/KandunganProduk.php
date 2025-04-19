<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KandunganProduk extends Model
{
    protected $table = 'kandungan_produk';

    protected $fillable = [
        'produks_id',
        'kandungan_id'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function resiko()
    {
        return $this->belongsTo(Resiko::class, 'resiko_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kandungans_id', 'id');
    }
}
