<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $fillable = [
        'alertable_id',
        'alertable_type',
        'title',
        'message',
        'severity',
        'status',
        'triggered_at',
        'resolved_at',
        'created_by',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    // 🔥 SEVERITY LEVELS
    public const SEVERITY_INFO     = 'info';
    public const SEVERITY_WARNING  = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    // 📌 STATUS
    public const STATUS_OPEN       = 'open';
    public const STATUS_RESOLVED   = 'resolved';
    public const STATUS_IGNORED    = 'ignored';

    // 🔁 RELATION POLYMORPHIQUE
    public function alertable()
    {
        return $this->morphTo();
    }

    // 👤 USER
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 📊 SCOPES
    public function scopeOpen(Builder $query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeCritical(Builder $query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    public function scopeRecent(Builder $query, int $limit = 5)
    {
        return $query->orderByDesc('triggered_at')->limit($limit);
    }

    // 🔐 Filtrage par utilisateur selon permissions (à utiliser dans le controller)
    public function scopeVisibleForUser(Builder $query, $user)
    {
        // Exemple simple : si admin, tout est visible
        if ($user->hasRole('admin')) {
            return $query;
        }
        // Sinon, seulement les alertes liées aux sites/equipements assignés
        return $query->whereHasMorph(
            'alertable',
            [SwitchModel::class, Router::class, Firewall::class, Site::class],
            function ($q) use ($user) {
                $q->whereIn('id', $user->assignedModelIds()); // méthode à créer côté User
            }
        );
    }

    // 🧠 LOGIQUE MÉTIER
    public function resolve()
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now()
        ]);
    }

    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    // 🎨 ACCESSOR UI
    public function getSeverityColorAttribute()
    {
        return match ($this->severity) {
            self::SEVERITY_INFO     => 'blue',
            self::SEVERITY_WARNING  => 'orange',
            self::SEVERITY_CRITICAL => 'red',
            default => 'gray'
        };
    }
}
