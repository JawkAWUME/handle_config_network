<?php
// app/Models/Firewall.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEncryptedCredentials;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Traits\Exportable;

class Firewall extends Model
{
    use HasEncryptedCredentials, Exportable, HasFactory, Notifiable;
    
    /**
     * Types de firewall supportés
     */
    const TYPE_PALO_ALTO = 'palo_alto';
    const TYPE_FORTINET = 'fortinet';
    const TYPE_CHECKPOINT = 'checkpoint';
    const TYPE_CISCO_ASA = 'cisco_asa';
    const TYPE_OTHER = 'other';

    /**
     * Les attributs qui sont assignables en masse
     */
    protected $fillable = [
        'name',
        'site_id',
        'user_id',
        'firewall_type',
        'brand',
        'model',
        'ip_nms',
        'ip_service',
        'vlan_nms',
        'vlan_service',
        'username',
        'password',
        'enable_password',
        'configuration',
        'configuration_file',
        'security_policies',
        'nat_rules',
        'vpn_configuration',
        'licenses',
        'firmware_version',
        'serial_number',
        'asset_tag',
        'status',
        'high_availability',
        'ha_peer_id',
        'monitoring_enabled',
        'last_backup',
        'notes'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'security_policies' => 'array',
        'nat_rules' => 'array',
        'vpn_configuration' => 'array',
        'licenses' => 'array',
        'last_backup' => 'datetime',
        'status' => 'boolean',
        'high_availability' => 'boolean',
        'monitoring_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être cachés dans les réponses
     */
    protected $hidden = [
        'password',
        'enable_password',
        'configuration',
        'vpn_configuration'
    ];

    /**
     * Relation avec le site
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
            return $this->belongsTo(User::class);
        }
    /**
     * Relation avec le pair en haute disponibilité
     */
    public function haPeer()
    {
        return $this->belongsTo(Firewall::class, 'ha_peer_id');
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
     * Scope pour les firewalls actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope pour les firewalls par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('firewall_type', $type);
    }

    /**
     * Scope pour les firewalls en haute disponibilité
     */
    public function scopeInHa($query)
    {
        return $query->where('high_availability', true);
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->brand} {$this->model})";
    }

    /**
     * Accesseur pour le type de firewall formaté
     */
    public function getFirewallTypeFormattedAttribute()
    {
        $types = [
            self::TYPE_PALO_ALTO => 'Palo Alto',
            self::TYPE_FORTINET => 'Fortinet',
            self::TYPE_CHECKPOINT => 'Check Point',
            self::TYPE_CISCO_ASA => 'Cisco ASA',
            self::TYPE_OTHER => 'Autre'
        ];

        return $types[$this->firewall_type] ?? 'Inconnu';
    }

    /**
     * Accesseur pour l'état de la haute disponibilité
     */
    public function getHaStatusAttribute()
    {
        if (!$this->high_availability) {
            return ['status' => 'secondary', 'message' => 'Non configuré'];
        }

        if ($this->haPeer && $this->haPeer->status) {
            return ['status' => 'success', 'message' => 'Actif avec pair'];
        } elseif ($this->haPeer) {
            return ['status' => 'warning', 'message' => 'Pair inactif'];
        } else {
            return ['status' => 'danger', 'message' => 'Pair non configuré'];
        }
    }

    /**
     * Méthode pour obtenir les politiques de sécurité
     */
    public function getSecurityPoliciesFormattedAttribute()
    {
        if (empty($this->security_policies)) {
            return [];
        }

        $policies = json_decode($this->security_policies, true);
        
        return array_map(function($policy) {
            return [
                'name' => $policy['name'] ?? 'Sans nom',
                'source_zone' => $policy['source_zone'] ?? 'any',
                'destination_zone' => $policy['destination_zone'] ?? 'any',
                'source_address' => $policy['source_address'] ?? 'any',
                'destination_address' => $policy['destination_address'] ?? 'any',
                'application' => $policy['application'] ?? 'any',
                'action' => $policy['action'] ?? 'deny',
                'enabled' => $policy['enabled'] ?? true,
                'description' => $policy['description'] ?? ''
            ];
        }, $policies);
    }

    /**
     * Méthode pour obtenir les règles NAT
     */
    public function getNatRulesFormattedAttribute()
    {
        if (empty($this->nat_rules)) {
            return [];
        }

        $rules = json_decode($this->nat_rules, true);
        
        return array_map(function($rule) {
            return [
                'name' => $rule['name'] ?? 'Sans nom',
                'type' => $rule['type'] ?? 'static',
                'original_source' => $rule['original_source'] ?? 'any',
                'original_destination' => $rule['original_destination'] ?? 'any',
                'translated_source' => $rule['translated_source'] ?? 'original',
                'translated_destination' => $rule['translated_destination'] ?? 'original',
                'service' => $rule['service'] ?? 'any',
                'enabled' => $rule['enabled'] ?? true
            ];
        }, $rules);
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
            'security_policies' => $this->security_policies,
            'nat_rules' => $this->nat_rules,
            'vpn_configuration' => $this->vpn_configuration,
            'user_id' => $userId ?? auth()->id(),
            'change_type' => 'backup',
            'notes' => $notes ?? 'Backup automatique'
        ]);

        $this->update(['last_backup' => now()]);

        return $backup;
    }

    /**
     * Méthode pour vérifier l'état des licences
     */
    public function checkLicenses()
    {
        if (empty($this->licenses)) {
            return ['status' => 'warning', 'message' => 'Aucune licence configurée'];
        }

        $licenses = json_decode($this->licenses, true);
        $expired = 0;
        $expiring = 0;
        $valid = 0;

        foreach ($licenses as $license) {
            if (isset($license['expiration_date'])) {
                $expiration = \Carbon\Carbon::parse($license['expiration_date']);
                $daysUntilExpiration = now()->diffInDays($expiration, false);
                
                if ($daysUntilExpiration < 0) {
                    $expired++;
                } elseif ($daysUntilExpiration <= 30) {
                    $expiring++;
                } else {
                    $valid++;
                }
            }
        }

        if ($expired > 0) {
            return ['status' => 'danger', 'message' => "{$expired} licence(s) expirée(s)"];
        } elseif ($expiring > 0) {
            return ['status' => 'warning', 'message' => "{$expiring} licence(s) expirent bientôt"];
        } else {
            return ['status' => 'success', 'message' => "Toutes les licences sont valides"];
        }
    }
}