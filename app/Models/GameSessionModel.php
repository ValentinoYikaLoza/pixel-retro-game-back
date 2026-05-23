<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSessionModel extends Model
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FINISHED = 'finished';
    const STATUS_ABANDONED = 'abandoned';

    use HasFactory;

    protected $table = 'game_session';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'game_id',
        'level',
        'status',
        'score',
        'seed',
        'food_eaten',
        'duration_ms',
        'exp_awarded',
        'coins_awarded',
        'started_at',
        'ended_at',
        'rewarded_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'game_id' => 'integer',
        'level' => 'integer',
        'status' => 'string',
        'score' => 'integer',
        'seed' => 'integer',
        'food_eaten' => 'integer',
        'duration_ms' => 'integer',
        'exp_awarded' => 'integer',
        'coins_awarded' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'rewarded_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(GameModel::class, 'game_id');
    }
}
