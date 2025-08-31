<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'division';
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
