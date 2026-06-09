<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'date',
        'location',
        'url',
        'description',
        'status',
        'featured_image',
        'is_featured',
        'registration_link'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_featured' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categorie::class); // Correction du nom du modèle
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->slug = static::generateUniqueSlug($event->title);
        });

        static::updating(function ($event) {
            if ($event->isDirty('title')) {
                $event->slug = static::generateUniqueSlug($event->title, $event->id);
            }
        });
    }

    private static function generateUniqueSlug($title, $eventId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($eventId, fn($query) => $query->where('id', '!=', $eventId))
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('date', '<', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y H:i');
    }

    public function getIsPastAttribute(): bool
    {
        return $this->date->isPast();
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image 
            ? asset('storage/' . $this->featured_image)
            : null;
    }

    // Méthodes métier
    public function publish(): void
    {
        $this->update(['status' => 'published']);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    public function markAsFeatured(): void
    {
        $this->update(['is_featured' => true]);
    }

    public function unmarkAsFeatured(): void
    {
        $this->update(['is_featured' => false]);
    }
}