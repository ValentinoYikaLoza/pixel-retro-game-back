<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameModel extends Model
{
    const SNAKE = 1;
    const TETRIS = 2;
    const PIXEL_INVADERS = 3;
    const PACMAN = 4;

    public $timestamps = false;

    use HasFactory;

    protected $table = 'game';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'title',
        'enabled',
        'lives_cost',
        'tick_ms',
        'grid_width',
        'grid_height',
    ];

    protected $casts = [
        'name' => 'string',
        'title' => 'string',
        'enabled' => 'boolean',
        'lives_cost' => 'integer',
        'tick_ms' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
