<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'videos';

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'url',
        'description',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categorie::class); // Assurez-vous que votre modèle de catégorie est bien "Categorie"
    }
}
