<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscriptions';

    // Statuts disponibles
    public const STATUS_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUS_VALIDE = 'VALIDE';
    public const STATUS_REJETE = 'REJETE';
    public const STATUS_SUSPENDU = 'SUSPENDU';

    protected $fillable = [
        'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'telephone', 'email', 'genre',
        'type_identification', 'numero_identification', 'numero_elecam',
        'region_id', 'departement_id', 'commune_id', 'bureau_vote_id', 'adresse_complete',
        'statut_professionnel',
        'est_inscrit_liste', 'annee_inscription', 'centre_vote', 'antenne_communale',
        'status', 'notes_rejet', 'validateur_id', 'date_validation',
        'consentement_rgpd', 'ip_inscription', 'user_agent',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_validation' => 'datetime',
        'est_inscrit_liste' => 'boolean',
        'consentement_rgpd' => 'boolean',
    ];

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function bureauVote()
    {
        return $this->belongsTo(BureauDeVote::class, 'bureau_vote_id', 'code');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    // Scopes
    public function scopeRecent($query, $days = 7)
    {
        return $query->whereDate('created_at', '>=', now()->subDays($days));
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_EN_ATTENTE);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDE);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJETE);
    }

    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeByBureau($query, $bureauId)
    {
        return $query->where('bureau_vote_id', $bureauId);
    }

    // Méthodes utiles
    public function getAgeAttribute()
    {
        return $this->date_naissance?->age;
    }

    public function getFullNameAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function isValidable()
    {
        return $this->status === self::STATUS_EN_ATTENTE
            && $this->numero_identification
            && $this->consentement_rgpd;
    }

    public function markAsValidated($validatorId)
    {
        $this->update([
            'status' => self::STATUS_VALIDE,
            'validateur_id' => $validatorId,
            'date_validation' => now(),
        ]);
    }

    public function markAsRejected($reason, $validatorId)
    {
        $this->update([
            'status' => self::STATUS_REJETE,
            'notes_rejet' => $reason,
            'validateur_id' => $validatorId,
            'date_validation' => now(),
        ]);
    }
}
