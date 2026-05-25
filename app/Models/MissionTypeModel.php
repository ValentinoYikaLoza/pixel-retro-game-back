<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionTypeModel extends Model
{
    const POINTS = 1;          // acumula puntos entre partidas
    const MATCHES = 2;         // jugar N partidas
    const SINGLE_GAME_SCORE = 3; // alcanzar N puntos en UNA sola partida

    public $timestamps = false;

    use HasFactory;

    protected $table = 'mission_type';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
