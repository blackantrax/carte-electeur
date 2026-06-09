<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlideshowImage extends Model
{
    use HasFactory;

    protected $fillable = ['slideshow_id', 'path', 'description'];

    public function slideshow()
    {
        return $this->belongsTo(Slideshow::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
