<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';
    protected $fillable = [
        'nama',
        'brand',
        'kategoris_id',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategoris_id');
    }
}
