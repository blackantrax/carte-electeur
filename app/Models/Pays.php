<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pays extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'nom_anglais',
        'continent',
        'indicatif_telephonique',
        'devise',
        'drapeau'
    ];

    public function ambassades(): HasMany
    {
        return $this->hasMany(Ambassade::class, 'pays_code', 'code');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'pays', 'code');
    }
}