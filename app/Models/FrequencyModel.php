<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequencyModel extends Model
{
    const MONTHLY = 1;
    const WEEKLY = 2;
    const DAILY = 3;

    public $timestamps = false;

    use HasFactory;

    protected $table = 'frequency';
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
