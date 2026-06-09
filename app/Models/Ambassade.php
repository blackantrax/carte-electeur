<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambassade extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'type',
        'pays_code',
        'ville',
        'adresse',
        'telephone',
        'email',
        'responsable',
        'nombre_electeurs'
    ];

    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'pays_code', 'code');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'ambassade', 'code');
    }
}