<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les statuts possibles pour un contact
     */
    const STATUS_UNREAD = 'unread';
    const STATUS_READ = 'read';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email', 
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'status'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Valeurs par défaut du modèle
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_UNREAD,
    ];

    /**
     * Prépare les données avant sauvegarde
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ip_address = request()->ip();
            $model->user_agent = request()->userAgent();
            $model->status = $model->status ?? self::STATUS_UNREAD;
        });
    }

    /**
     * Scope pour les messages non lus
     */
    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    /**
     * Scope pour les messages lus
     */
    public function scopeRead($query)
    {
        return $query->where('status', self::STATUS_READ);
    }

    /**
     * Scope pour les messages archivés
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }
}