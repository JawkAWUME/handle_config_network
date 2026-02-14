<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Backup extends Model
{
    use HasFactory;

    protected $table = 'backups';

    protected $fillable = [
        'backupable_id',
        'backupable_type',
        'filename',
        'path',
        'size',
        'status',
        'hash',
        'executed_at',
        'created_by',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'size'        => 'integer',
    ];

    // 🔐 STATUTS
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_PENDING = 'pending';

    // 🔁 RELATION POLYMORPHIQUE
    public function backupable()
    {
        return $this->morphTo();
    }

    // 👤 USER QUI A LANCÉ LE BACKUP
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 📊 SCOPES
    public function scopeSuccess(Builder $query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed(Builder $query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRecent(Builder $query, int $limit = 5)
    {
        return $query->orderByDesc('executed_at')->limit($limit);
    }

    // 🔐 Filtrage par utilisateur selon permissions
    public function scopeVisibleForUser(Builder $query, $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }
        return $query->whereHasMorph(
            'backupable',
            [SwitchModel::class, Router::class, Firewall::class],
            function ($q) use ($user) {
                $q->whereIn('id', $user->assignedModelIds()); // méthode côté User
            }
        );
    }

    // 📦 ACCESSORS
    public function getSizeFormattedAttribute()
    {
        return number_format($this->size / 1024, 2) . ' KB';
    }

    public function getIsSuccessfulAttribute()
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
