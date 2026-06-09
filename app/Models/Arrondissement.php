<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrondissement extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'commune_code',
        'chef_lieu'
    ];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_code', 'code');
    }

    public function bureauxDeVote(): HasMany
    {
        return $this->hasMany(BureauDeVote::class, 'arrondissement_code', 'code');
    }
}