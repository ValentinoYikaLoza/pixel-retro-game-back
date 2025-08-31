<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'game';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'title'
    ];

    protected $casts = [
        'name' => 'string',
        'title' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
