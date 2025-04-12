<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $fillable = [
        'nama_kategori',
        'created_at',
        'updated_at',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
