<?php
// app/Models/ConfigurationHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class ConfigurationHistory extends Model
{
    use HasFactory, Notifiable;
    /**
     * Types de changement supportés
     */
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_BACKUP = 'backup';
    const TYPE_RESTORE = 'restore';
    const TYPE_AUTO_BACKUP = 'auto_backup';
    const TYPE_MANUAL_BACKUP = 'manual_backup';

    /**
     * Les attributs qui sont assignables en masse
     */
    protected $fillable = [
        'device_type',
        'device_id',
        'configuration',
        'configuration_file',
        'config_size',
        'config_checksum',
        'user_id',
        'change_type',
        'notes',
        'restored_from',
        'ip_address',
        'change_summary',
        'pre_change_config',
        'post_change_config'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'config_size' => 'integer'
    ];

    /**
     * Les attributs qui doivent être cachés dans les réponses
     */
    protected $hidden = [
        'configuration',
        'pre_change_config',
        'post_change_config'
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Calculer la taille et le checksum avant de sauvegarder
        static::creating(function ($history) {
            if ($history->configuration) {
                $history->config_size = strlen($history->configuration);
                $history->config_checksum = md5($history->configuration);
            }
        });
    }

    /**
     * Relation polymorphique avec les équipements
     */
    public function device()
    {
        return $this->morphTo();
    }

    /**
     * Relation avec l'utilisateur qui a effectué le changement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le backup restauré
     */
    public function restoredFrom()
    {
        return $this->belongsTo(ConfigurationHistory::class, 'restored_from');
    }

    /**
     * Relation avec les backups qui ont restauré cette configuration
     */
    public function restoredIn()
    {
        return $this->hasMany(ConfigurationHistory::class, 'restored_from');
    }

    /**
     * Scope pour les backups seulement
     */
    public function scopeBackups($query)
    {
        return $query->whereIn('change_type', ['backup', 'auto_backup', 'manual_backup']);
    }

    /**
     * Scope pour les changements manuels
     */
    public function scopeManualChanges($query)
    {
        return $query->whereIn('change_type', ['create', 'update', 'restore']);
    }

    /**
     * Scope pour un type d'équipement spécifique
     */
    public function scopeForDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope pour un équipement spécifique
     */
    public function scopeForDevice($query, $deviceType, $deviceId)
    {
        return $query->where('device_type', $deviceType)
                    ->where('device_id', $deviceId);
    }

    /**
     * Scope pour les changements récents
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accesseur pour le type de changement formaté
     */
    public function getChangeTypeFormattedAttribute()
    {
        $types = [
            self::TYPE_CREATE => 'Création',
            self::TYPE_UPDATE => 'Mise à jour',
            self::TYPE_BACKUP => 'Backup',
            self::TYPE_RESTORE => 'Restauration',
            self::TYPE_AUTO_BACKUP => 'Backup automatique',
            self::TYPE_MANUAL_BACKUP => 'Backup manuel'
        ];

        return $types[$this->change_type] ?? 'Inconnu';
    }

    /**
     * Accesseur pour le nom de l'équipement
     */
    public function getDeviceNameAttribute()
    {
        if ($this->device) {
            return $this->device->name;
        }
        
        return "Équipement supprimé";
    }

    /**
     * Accesseur pour le type d'équipement formaté
     */
    public function getDeviceTypeFormattedAttribute()
    {
        $types = [
            SwitchModel::class => 'Switch',
            Router::class => 'Routeur',
            Firewall::class => 'Firewall'
        ];

        return $types[$this->device_type] ?? 'Inconnu';
    }

    /**
     * Accesseur pour la taille formatée de la configuration
     */
    public function getConfigSizeFormattedAttribute()
    {
        $size = $this->config_size;
        
        if ($size < 1024) {
            return $size . ' octets';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' Ko';
        } else {
            return round($size / 1048576, 2) . ' Mo';
        }
    }

    /**
     * Méthode pour obtenir un diff entre deux configurations
     */
    public function getDiffWith($otherHistory)
    {
        if (!$this->configuration || !$otherHistory->configuration) {
            return 'Impossible de comparer: configuration manquante';
        }

        $diff = '';
        $lines1 = explode("\n", $this->configuration);
        $lines2 = explode("\n", $otherHistory->configuration);
        
        $length = max(count($lines1), count($lines2));
        
        for ($i = 0; $i < $length; $i++) {
            $line1 = $lines1[$i] ?? '';
            $line2 = $lines2[$i] ?? '';
            
            if ($line1 !== $line2) {
                $diff .= sprintf(
                    "Ligne %d: - %s\n          + %s\n",
                    $i + 1,
                    $line1,
                    $line2
                );
            }
        }
        
        return $diff;
    }

    /**
     * Méthode pour télécharger la configuration
     */
    public function downloadConfig()
    {
        $filename = sprintf(
            'config_%s_%d_%s.txt',
            class_basename($this->device_type),
            $this->device_id,
            $this->created_at->format('Y-m-d_His')
        );
        
        return [
            'filename' => $filename,
            'content' => $this->configuration,
            'content_type' => 'text/plain'
        ];
    }

    /**
     * Méthode pour vérifier si la configuration est corrompue
     */
    public function isCorrupted()
    {
        if (!$this->configuration || !$this->config_checksum) {
            return true;
        }
        
        return md5($this->configuration) !== $this->config_checksum;
    }

    /**
     * Méthode pour obtenir les métadonnées du changement
     */
    public function getChangeMetadataAttribute()
    {
        return [
            'user' => $this->user ? $this->user->name : 'Inconnu',
            'device' => $this->device_name,
            'device_type' => $this->device_type_formatted,
            'change_type' => $this->change_type_formatted,
            'timestamp' => $this->created_at->format('d/m/Y H:i:s'),
            'size' => $this->config_size_formatted,
            'notes' => $this->notes,
            'restored_from' => $this->restored_from ? $this->restoredFrom->id : null,
            'ip_address' => $this->ip_address
        ];
    }
}