<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGameLevelModel extends Model
{
    use HasFactory;

    protected $table = 'user_game_level';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'game_level_id',
        'best_score',
        'cleared_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'game_level_id' => 'integer',
        'best_score' => 'integer',
        'cleared_at' => 'datetime',
    ];
}
