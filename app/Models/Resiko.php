<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resiko extends Model
{
    protected $table = 'resikos';

    protected $fillable = [
        
        'code',
        'tingkat_resiko',
        'deskripsi',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'resikos_id', 'id');
    }
}
