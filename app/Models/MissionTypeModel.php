<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionTypeModel extends Model
{
    const POINTS = 1;
    const MATCHES = 2;
    const STREAK = 3;
    const MULTIGAME = 4;
    const EXACT = 5;

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
