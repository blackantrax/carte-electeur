<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BureauDeVote extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'adresse',
        'commune_code',
        'arrondissement_code',
        'nombre_electeurs',
        'localisation',
        'description'
    ];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_code', 'code');
    }

    public function arrondissement(): BelongsTo
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissement_code', 'code');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'bureau_vote_id', 'code');
    }
}