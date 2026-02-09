<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Which columns can be mass-assigned
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
    ];

    // One category has many gifts
    public function gifts()
    {
        return $this->hasMany(Gift::class);
    }
}
