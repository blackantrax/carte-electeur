<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categorie;

class Slideshow extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'category_id', 'status'];

    public function category()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function images()
    {
        return $this->hasMany(SlideshowImage::class);
    }
}
