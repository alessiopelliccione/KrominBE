<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'tipologia', 'list_name', 'shortcode'];

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
