<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Region extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'chef_lieu',
        'population',
        'superficie',
        'description',
        'pays_code', // Ajout du champ étranger
    ];

    /**
     * Une région appartient à un pays.
     */
    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'pays_code', 'code');
    }

    /**
     * Une région a plusieurs départements.
     */
    public function departements(): HasMany
    {
        return $this->hasMany(Departement::class, 'region_code', 'code');
    }
}