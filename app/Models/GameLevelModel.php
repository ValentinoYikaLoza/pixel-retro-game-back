<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLevelModel extends Model
{
    use HasFactory;

    protected $table = 'game_level';
    protected $primaryKey = 'id';

    protected $fillable = [
        'game_id',
        'level',
        'tick_ms',
        'grid_width',
        'grid_height',
        'wrap_around',
        'walls',
        'target_score',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'level' => 'integer',
        'tick_ms' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer',
        'wrap_around' => 'boolean',
        'walls' => 'array',
        'target_score' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
