<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KandunganProduk extends Model
{

    protected $fillable = [
        'produks_id',
        'kandungans_id'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function resiko()
    {
        return $this->belongsTo(Resiko::class, 'resiko_id');
    }
    public function kandungan()
    {
        return $this->belongsTo(Kandungan::class, 'kandungans_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'produks_id', 'id');
    }
}
