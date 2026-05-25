<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGameStatModel extends Model
{
    use HasFactory;

    protected $table = 'user_game_stat';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'game_id',
        'high_score',
        'infinite_high_score',
        'total_games',
        'total_score',
        'total_food',
        'last_played_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'game_id' => 'integer',
        'high_score' => 'integer',
        'infinite_high_score' => 'integer',
        'total_games' => 'integer',
        'total_score' => 'integer',
        'total_food' => 'integer',
        'last_played_at' => 'datetime',
    ];
}
