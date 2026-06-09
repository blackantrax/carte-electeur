<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'nom',
        'chef_lieu',
        'region_code',
        'population',
        'superficie'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_code', 'code');
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'departement_code', 'code');
    }
}