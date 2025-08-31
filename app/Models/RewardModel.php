<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardModel extends Model
{
    const GOLD = 1;
    const SILVER = 2;
    const BRONZE = 3;

    public $timestamps = false;

    use HasFactory;

    protected $table = 'reward';
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
