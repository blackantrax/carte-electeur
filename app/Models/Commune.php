<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'type',
        'departement_code', // Corrigé pour correspondre à la colonne de la table
        'maire',
        'population'
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_code', 'code');
    }

    public function arrondissements(): HasMany
    {
        return $this->hasMany(Arrondissement::class, 'commune_code', 'code');
    }

    public function bureauxDeVote(): HasMany
    {
        return $this->hasMany(BureauDeVote::class, 'commune_code', 'code');
    }
}