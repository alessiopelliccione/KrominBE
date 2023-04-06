<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasFactory;
    protected $fillable = ['tipologia', 'list_name', 'user_id', 'shortcode'];

    const PUBBLICA = 'pubblica';
    const PRIVATA = 'privata';

    const TIPOLOGIA = [
        self::PUBBLICA,
        self::PRIVATA
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
