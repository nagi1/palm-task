<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'image_url',
        'url',
        'source',
        'asin',
        'currency',
        'raw_payload',
    ];

    protected $casts = [
        'price' => 'float',
        'raw_payload' => 'array',
    ];
}
