<?php
// app/Models/Router.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEncryptedCredentials;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Router extends Model
{
    use HasEncryptedCredentials, Searchable,HasFactory, Notifiable;
    
    /**
     * Les attributs qui sont assignables en masse
     */
    protected $fillable = [
        'name',
        'site_id',
        'brand',
        'model',
        'interfaces',
        'routing_protocols',
        'management_ip',
        'vlan_nms',
        'vlan_service',
        'username',
        'password',
        'configuration',
        'configuration_file',
        'operating_system',
        'serial_number',
        'asset_tag',
        'status',
        'last_backup',
        'notes'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'interfaces' => 'array',
        'routing_protocols' => 'array',
        'last_backup' => 'datetime',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être cachés dans les réponses
     */
    protected $hidden = [
        'password',
        'configuration'
    ];

    /**
     * Relation avec le site
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Relation avec l'historique des configurations
     */
    public function configurationHistories()
    {
        return $this->morphMany(ConfigurationHistory::class, 'device');
    }

    /**
     * Relation avec les logs d'accès
     */
    public function accessLogs()
    {
        return $this->morphMany(AccessLog::class, 'device');
    }

    /**
     * Scope pour les routeurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope pour les routeurs par marque
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Scope pour les routeurs nécessitant un backup
     */
    public function scopeNeedsBackup($query, $days = 7)
    {
        return $query->where(function($q) use ($days) {
            $q->whereNull('last_backup')
              ->orWhere('last_backup', '<', now()->subDays($days));
        });
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->brand} {$this->model})";
    }

    /**
     * Accesseur pour l'état du backup
     */
    public function getBackupStatusAttribute()
    {
        if (!$this->last_backup) {
            return ['status' => 'warning', 'message' => 'Jamais sauvegardé'];
        }

        $daysSinceLastBackup = $this->last_backup->diffInDays(now());

        if ($daysSinceLastBackup <= 1) {
            return ['status' => 'success', 'message' => 'Récent (<24h)'];
        } elseif ($daysSinceLastBackup <= 7) {
            return ['status' => 'info', 'message' => 'Récent (<7 jours)'];
        } else {
            return ['status' => 'danger', 'message' => 'Ancien (>7 jours)'];
        }
    }

    /**
     * Méthode pour obtenir les interfaces sous forme de tableau structuré
     */
    public function getFormattedInterfacesAttribute()
    {
        if (empty($this->interfaces)) {
            return [];
        }

        $interfaces = json_decode($this->interfaces, true);
        
        if (!is_array($interfaces)) {
            return [];
        }

        return array_map(function($interface) {
            return [
                'name' => $interface['name'] ?? 'Inconnue',
                'ip_address' => $interface['ip_address'] ?? 'Non configurée',
                'subnet_mask' => $interface['subnet_mask'] ?? '/24',
                'description' => $interface['description'] ?? '',
                'status' => $interface['status'] ?? 'down',
                'vlan' => $interface['vlan'] ?? null,
                'speed' => $interface['speed'] ?? '1G'
            ];
        }, $interfaces);
    }

    /**
     * Méthode pour créer un backup de configuration
     */
    public function createBackup($userId = null, $notes = null)
    {
        $backup = ConfigurationHistory::create([
            'device_type' => self::class,
            'device_id' => $this->id,
            'configuration' => $this->configuration,
            'configuration_file' => $this->configuration_file,
            'user_id' => $userId ?? auth()->id(),
            'change_type' => 'backup',
            'notes' => $notes ?? 'Backup automatique'
        ]);

        $this->update(['last_backup' => now()]);

        return $backup;
    }

    /**
     * Méthode pour restaurer une configuration depuis l'historique
     */
    public function restoreFromBackup($backupId)
    {
        $backup = ConfigurationHistory::findOrFail($backupId);
        
        if ($backup->device_type !== self::class || $backup->device_id !== $this->id) {
            throw new \Exception('Backup non valide pour cet équipement');
        }

        // Créer un backup avant restauration
        $preRestoreBackup = $this->createBackup(null, 'Pré-restauration');
        
        // Restaurer la configuration
        $this->update([
            'configuration' => $backup->configuration,
            'configuration_file' => $backup->configuration_file
        ]);

        // Enregistrer l'action de restauration
        ConfigurationHistory::create([
            'device_type' => self::class,
            'device_id' => $this->id,
            'configuration' => $backup->configuration,
            'configuration_file' => $backup->configuration_file,
            'user_id' => auth()->id(),
            'change_type' => 'restore',
            'notes' => "Restauration depuis backup #{$backupId}",
            'restored_from' => $backupId
        ]);

        return $preRestoreBackup;
    }
}