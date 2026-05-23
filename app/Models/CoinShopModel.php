<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinShopModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'coin_shop';
    protected $primaryKey = 'id';
    protected $fillable = [
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
