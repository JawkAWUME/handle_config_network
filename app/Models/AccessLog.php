<?php
// app/Models/AccessLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
class AccessLog extends Model
{
    use HasFactory, Notifiable;
    /**
     * Types d'accès supportés
     */
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_VIEW = 'view';
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_BACKUP = 'backup';
    const TYPE_RESTORE = 'restore';
    const TYPE_EXPORT = 'export';

    /**
     * Résultats d'accès
     */
    const RESULT_SUCCESS = 'success';
    const RESULT_FAILED = 'failed';
    const RESULT_DENIED = 'denied';

    /**
     * Les attributs qui sont assignables en masse
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'action',
        'device_type',
        'device_id',
        'parameters',
        'response_code',
        'response_time',
        'result',
        'error_message',
        'referrer',
        'country',
        'city',
        'latitude',
        'longitude',
        'isp',
        'browser',
        'platform',
        'device_family'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'parameters' => 'array',
        'response_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être cachés dans les réponses
     */
    protected $hidden = [
        'parameters'
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Enrichir les informations avant de sauvegarder
        static::creating(function ($log) {
            $log->enrichGeoData();
            $log->enrichBrowserData();
            $log->calculateResponseTime();
        });
    }

            /**
         * Récupérer les constantes par préfixe (TYPE_, RESULT_, etc.)
         */
        public static function getConstants(string $prefix): array
        {
            $reflection = new \ReflectionClass(self::class);
            $constants = $reflection->getConstants();

            return array_values(
                array_filter(
                    $constants,
                    fn ($key) => str_starts_with($key, $prefix),
                    ARRAY_FILTER_USE_KEY
                )
            );
        }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation polymorphique avec les équipements
     */
    public function device()
    {
        return $this->morphTo();
    }

    /**
     * Scope pour les accès réussis
     */
    public function scopeSuccessful($query)
    {
        return $query->where('result', self::RESULT_SUCCESS);
    }

    /**
     * Scope pour les accès échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('result', self::RESULT_FAILED);
    }

    /**
     * Scope pour les accès refusés
     */
    public function scopeDenied($query)
    {
        return $query->where('result', self::RESULT_DENIED);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour une IP spécifique
     */
    public function scopeForIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope pour une période spécifique
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope pour les actions spécifiques
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour les activités suspectes
     */
    public function scopeSuspicious($query)
    {
        return $query->where(function($q) {
            $q->where('result', self::RESULT_DENIED)
              ->orWhere('result', self::RESULT_FAILED)
              ->orWhere('response_code', '>=', 400);
        });
    }

    /**
     * Accesseur pour l'action formatée
     */
    public function getActionFormattedAttribute()
    {
        $actions = [
            self::TYPE_LOGIN => 'Connexion',
            self::TYPE_LOGOUT => 'Déconnexion',
            self::TYPE_VIEW => 'Consultation',
            self::TYPE_CREATE => 'Création',
            self::TYPE_UPDATE => 'Mise à jour',
            self::TYPE_DELETE => 'Suppression',
            self::TYPE_BACKUP => 'Backup',
            self::TYPE_RESTORE => 'Restauration',
            self::TYPE_EXPORT => 'Export'
        ];

        return $actions[$this->action] ?? 'Inconnue';
    }

    /**
     * Accesseur pour le résultat formaté
     */
    public function getResultFormattedAttribute()
    {
        $results = [
            self::RESULT_SUCCESS => 'Succès',
            self::RESULT_FAILED => 'Échec',
            self::RESULT_DENIED => 'Refusé'
        ];

        return $results[$this->result] ?? 'Inconnu';
    }

    /**
     * Accesseur pour la méthode HTTP formatée
     */
    public function getMethodFormattedAttribute()
    {
        $methods = [
            'GET' => 'Consultation',
            'POST' => 'Création',
            'PUT' => 'Mise à jour',
            'PATCH' => 'Modification',
            'DELETE' => 'Suppression'
        ];

        return $methods[$this->method] ?? $this->method;
    }

    /**
     * Accesseur pour les paramètres formatés
     */
    public function getParametersFormattedAttribute()
    {
        if (empty($this->parameters)) {
            return 'Aucun paramètre';
        }

        // Masquer les paramètres sensibles
        $sensitiveFields = ['password', 'token', 'secret', 'key', 'credential'];
        $parameters = $this->parameters;
        
        foreach ($parameters as $key => $value) {
            foreach ($sensitiveFields as $field) {
                if (stripos($key, $field) !== false) {
                    $parameters[$key] = '***MASQUÉ***';
                }
            }
        }

        return json_encode($parameters, JSON_PRETTY_PRINT);
    }

    /**
     * Accesseur pour la durée de réponse formatée
     */
    public function getResponseTimeFormattedAttribute()
    {
        if (!$this->response_time) {
            return 'N/A';
        }

        return round($this->response_time, 2) . ' ms';
    }

    /**
     * Méthode pour enrichir les données géographiques
     */
    protected function enrichGeoData()
    {
        // Utiliser un service de géolocalisation (ex: ip-api.com, ipstack.com)
        // Note: En production, utilisez un service payant ou une base de données locales
        try {
            // Exemple avec ip-api.com (limite 45 requêtes/minute)
            if ($this->ip_address && !in_array($this->ip_address, ['127.0.0.1', '::1'])) {
                $response = @file_get_contents("http://ip-api.com/json/{$this->ip_address}?fields=status,country,city,lat,lon,isp");
                
                if ($response) {
                    $data = json_decode($response, true);
                    
                    if ($data['status'] === 'success') {
                        $this->country = $data['country'] ?? null;
                        $this->city = $data['city'] ?? null;
                        $this->latitude = $data['lat'] ?? null;
                        $this->longitude = $data['lon'] ?? null;
                        $this->isp = $data['isp'] ?? null;
                    }
                }
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la journalisation en cas d'erreur de géolocalisation
        }
    }

    /**
     * Méthode pour enrichir les données du navigateur
     */
    protected function enrichBrowserData()
    {
        if (!$this->user_agent) {
            return;
        }

        // Utiliser une librairie comme jenssegers/agent ou analyser manuellement
        $userAgent = $this->user_agent;
        
        // Détection simple du navigateur
        if (strpos($userAgent, 'Chrome') !== false) {
            $this->browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $this->browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $this->browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $this->browser = 'Edge';
        } else {
            $this->browser = 'Autre';
        }

        // Détection simple de la plateforme
        if (strpos($userAgent, 'Windows') !== false) {
            $this->platform = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $this->platform = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $this->platform = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $this->platform = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $this->platform = 'iOS';
        } else {
            $this->platform = 'Autre';
        }
    }

    /**
     * Méthode pour calculer le temps de réponse
     */
    protected function calculateResponseTime()
    {
        // Cette méthode devrait être appelée après la réponse
        // Dans Laravel, vous pouvez utiliser un middleware pour capturer le temps de réponse
        // Pour l'instant, nous le laissons vide
    }

    /**
     * Méthode pour détecter un comportement suspect
     */
    public function isSuspicious()
    {
        $suspicious = false;
        
        // Trop d'échecs de connexion
        if ($this->action === self::TYPE_LOGIN && $this->result === self::RESULT_FAILED) {
            $suspicious = true;
        }
        
        // Accès refusé
        if ($this->result === self::RESULT_DENIED) {
            $suspicious = true;
        }
        
        // IP suspecte (tor, proxy, etc.)
        $suspiciousIps = [
            '185.220.100.',  // Tor exit nodes (exemple)
            '10.', '172.16.', '192.168.'  // IPs privées (si venant de l'extérieur)
        ];
        
        foreach ($suspiciousIps as $ip) {
            if (strpos($this->ip_address, $ip) === 0) {
                $suspicious = true;
                break;
            }
        }
        
        return $suspicious;
    }

    /**
     * Méthode pour générer un rapport d'activité
     */
    public function getActivityReport()
    {
        return [
            'user' => $this->user ? $this->user->name : 'Anonyme',
            'action' => $this->action_formatted,
            'device' => $this->device_type ? 
                ($this->device ? $this->device->name : 'Équipement supprimé') : 'N/A',
            'url' => $this->url,
            'method' => $this->method_formatted,
            'result' => $this->result_formatted,
            'response_code' => $this->response_code,
            'response_time' => $this->response_time_formatted,
            'ip_address' => $this->ip_address,
            'location' => $this->city ? "{$this->city}, {$this->country}" : 'Inconnue',
            'timestamp' => $this->created_at->format('d/m/Y H:i:s'),
            'browser' => $this->browser,
            'platform' => $this->platform,
            'suspicious' => $this->isSuspicious()
        ];
    }
}