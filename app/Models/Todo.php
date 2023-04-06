<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'content', 'status', 'position', 'list'];

    const PENDING = 'pending';
    const DONE = 'done';

    const STATUSES = [
        self::PENDING,
        self::DONE
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
