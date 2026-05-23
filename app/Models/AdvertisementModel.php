<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'advertisement';
    protected $primaryKey = 'id';
    protected $fillable = [
        'reward',
        'reward_type',
    ];

    protected $casts = [
        'reward' => 'integer',
        'reward_type' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
