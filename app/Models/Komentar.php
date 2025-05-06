<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    protected $fillable = [
        'user_id',
        'posting_id',
        'komentar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postingan()
    {
        return $this->belongsTo(Postingan::class);
    }
}
