<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'content',
        'featured_image',
        'status',
        'main',
        'secondary',
        'featured', // Ajouté pour cohérence avec votre formulaire
        'allow_comments' // Ajouté pour cohérence avec votre formulaire
    ];

    protected $casts = [
        'category_id' => 'integer',
        'main' => 'boolean',
        'secondary' => 'boolean',
        'featured' => 'boolean', // Ajouté
        'allow_comments' => 'boolean', // Ajouté
        'status' => 'string',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s' // Ajouté pour cohérence
    ];

    protected $appends = [
        'featured_image_url',
        'is_published'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        
        return Storage::disk('public')->exists($this->featured_image) 
            ? Storage::url($this->featured_image)
            : null;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Nouveau : supprime le fichier image associé
    protected static function booted()
    {
        static::deleting(function ($article) {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
        });
    }
}