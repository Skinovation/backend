<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisProduk extends Model
{
    protected $table = 'analisis_produk';

    protected $fillable = [
        'user_id',
        'produk_id',
        'analisis',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
