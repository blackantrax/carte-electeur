<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories'; // Nom de la table

    protected $fillable = ['name', 'slug', 'type']; // Ajout du champ 'type'

    public $timestamps = true; // Active les timestamps (created_at, updated_at)

    /**
     * Boot method pour générer automatiquement le slug.
     */
    protected static function boot()
    {
        parent::boot();

        // Création d'une catégorie : génération automatique du slug
        static::creating(function ($categorie) {
            $categorie->slug = Str::slug($categorie->name); // Génération automatique du slug
        });

        // Mise à jour d'une catégorie : mise à jour automatique du slug
        static::updating(function ($categorie) {
            $categorie->slug = Str::slug($categorie->name); // Mise à jour automatique du slug
        });

        // Suppression d'une catégorie : vérification et gestion des relations si nécessaire
        static::deleting(function ($categorie) {
            // Vous pouvez ajouter une logique ici si vous avez des relations
            // qui doivent être supprimées en même temps que la catégorie.
            // Par exemple :
            // $categorie->articles()->delete();
        });
    }
}
