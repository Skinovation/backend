<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kandungan extends Model
{
    protected $table = 'kandungans';

    protected $fillable = [
        'name',
        'fungsi',
        'resiko_id',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kandungans_id', 'id');
    }
}
