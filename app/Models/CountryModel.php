<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'country';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'flag',
        'code',
        'dial_code',
        'mask'
    ];

    protected $casts = [
        'name' => 'string',
        'flag' => 'string',
        'code' => 'string',
        'dial_code' => 'string',
        'mask' => 'string'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
