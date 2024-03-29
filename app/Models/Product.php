<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'price',
    ];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class,'user_products');
    }


}
