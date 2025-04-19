<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
        protected $fillable = [
        'nama',
        'created_at',
        'updated_at',
    ];

    public function kandungan()
    {
        return $this->hasMany(Produk::class, 'kategori_id', 'id');
    }
}
