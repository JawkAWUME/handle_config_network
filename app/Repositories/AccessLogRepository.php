<?php
// app/Repositories/AccessLogRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\AccessLogRepositoryInterface;
use App\Models\AccessLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccessLogRepository implements AccessLogRepositoryInterface
{
    protected $model;
    protected $cacheTtl = 1800; // 30 minutes

    public function __construct(AccessLog $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return Cache::remember('access_logs.all', $this->cacheTtl, function () {
            return $this->model->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->get();
        });
    }

    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?AccessLog
    {
        return Cache::remember("access_log.{$id}", $this->cacheTtl, function () use ($id) {
            return $this->model->with('user')->find($id);
        });
    }

    public function findOrFail(int $id): AccessLog
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function create(array $data): AccessLog
    {
        $log = $this->model->create($data);
        $this->clearAccessLogCache();
        return $log;
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = $this->model->with('user');

        if (!empty($query)) {
            $search->where(function ($q) use ($query) {
                $q->where('url', 'like', "%{$query}%")
                  ->orWhere('ip_address', 'like', "%{$query}%")
                  ->orWhere('user_agent', 'like', "%{$query}%")
                  ->orWhere('action', 'like', "%{$query}%");
            });
        }

        // Appliquer les filtres
        if (!empty($filters['user_id'])) {
            $search->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['ip_address'])) {
            $search->where('ip_address', $filters['ip_address']);
        }

        if (!empty($filters['action'])) {
            $search->where('action', $filters['action']);
        }

        if (!empty($filters['result'])) {
            $search->where('result', $filters['result']);
        }

        if (!empty($filters['response_code'])) {
            $search->where('response_code', $filters['response_code']);
        }

        if (!empty($filters['start_date'])) {
            $search->where('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $search->where('created_at', '<=', $filters['end_date']);
        }

        return $search->orderBy('created_at', 'desc')->paginate(50);
    }

    public function getForUser(int $userId): Collection
    {
        return Cache::remember("access_logs.user.{$userId}", $this->cacheTtl, function () use ($userId) {
            return $this->model->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        });
    }

    public function getForIp(string $ipAddress): Collection
    {
        return Cache::remember("access_logs.ip.{$ipAddress}", $this->cacheTtl, function () use ($ipAddress) {
            return $this->model->where('ip_address', $ipAddress)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        });
    }

    public function getBetweenDates(string $startDate, string $endDate): Collection
    {
        $cacheKey = "access_logs.dates.{$startDate}.{$endDate}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($startDate, $endDate) {
            return $this->model->whereBetween('created_at', [$startDate, $endDate])
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function getSuspiciousActivities(): Collection
    {
        return Cache::remember('access_logs.suspicious', 300, function () {
            return $this->model->where(function ($query) {
                    $query->where('result', AccessLog::RESULT_DENIED)
                          ->orWhere('result', AccessLog::RESULT_FAILED)
                          ->orWhere('response_code', '>=', 400);
                })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
        });
    }

    public function getStatistics(): array
    {
        return Cache::remember('access_logs.statistics', $this->cacheTtl, function () {
            $total = $this->model->count();
            $today = $this->model->whereDate('created_at', Carbon::today())->count();
            $yesterday = $this->model->whereDate('created_at', Carbon::yesterday())->count();
            
            // Par résultat
            $byResult = $this->model->select('result', DB::raw('count(*) as count'))
                ->groupBy('result')
                ->orderBy('count', 'desc')
                ->get();

            // Par action
            $byAction = $this->model->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Par code de réponse
            $byResponseCode = $this->model->select('response_code', DB::raw('count(*) as count'))
                ->groupBy('response_code')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Par IP
            $byIp = $this->model->select('ip_address', DB::raw('count(*) as count'))
                ->groupBy('ip_address')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Activités suspectes
            $suspicious = $this->model->where(function ($query) {
                $query->where('result', AccessLog::RESULT_DENIED)
                      ->orWhere('result', AccessLog::RESULT_FAILED)
                      ->orWhere('response_code', '>=', 400);
            })->count();

            return [
                'total' => $total,
                'today' => $today,
                'yesterday' => $yesterday,
                'change_percentage' => $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0,
                'by_result' => $byResult,
                'by_action' => $byAction,
                'by_response_code' => $byResponseCode,
                'by_ip' => $byIp,
                'suspicious_activities' => $suspicious,
                'suspicious_percentage' => $total > 0 ? round(($suspicious / $total) * 100, 1) : 0
            ];
        });
    }

    public function getByAction(string $action): Collection
    {
        return Cache::remember("access_logs.action.{$action}", $this->cacheTtl, function () use ($action) {
            return $this->model->where('action', $action)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        });
    }

    public function getByResult(string $result): Collection
    {
        return Cache::remember("access_logs.result.{$result}", $this->cacheTtl, function () use ($result) {
            return $this->model->where('result', $result)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(200)
                ->get();
        });
    }

    public function getFailedLoginAttempts(int $hours = 24): Collection
    {
        return Cache::remember("access_logs.failed_logins.{$hours}", 300, function () use ($hours) {
            return $this->model->where('action', AccessLog::TYPE_LOGIN)
                ->where('result', AccessLog::RESULT_FAILED)
                ->where('created_at', '>=', Carbon::now()->subHours($hours))
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function isIpSuspicious(string $ipAddress): bool
    {
        // Vérifier les échecs de connexion récents
        $failedAttempts = $this->model->where('ip_address', $ipAddress)
            ->where('action', AccessLog::TYPE_LOGIN)
            ->where('result', AccessLog::RESULT_FAILED)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        // Vérifier les accès refusés
        $deniedAccess = $this->model->where('ip_address', $ipAddress)
            ->where('result', AccessLog::RESULT_DENIED)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        // IP suspecte si plus de 5 échecs de connexion ou 3 accès refusés
        return $failedAttempts > 5 || $deniedAccess > 3;
    }

    public function generateUserActivityReport(int $userId): array
    {
        $userLogs = $this->getForUser($userId);
        
        $report = [
            'user_id' => $userId,
            'total_actions' => $userLogs->count(),
            'period' => [
                'start' => $userLogs->last()->created_at ?? Carbon::now(),
                'end' => $userLogs->first()->created_at ?? Carbon::now()
            ],
            'by_action' => [],
            'by_result' => [],
            'by_hour' => [],
            'recent_activity' => [],
            'suspicious_activity' => []
        ];

        // Analyser par action
        foreach ($userLogs as $log) {
            $action = $log->action;
            $result = $log->result;
            $hour = $log->created_at->hour;
            
            // Compter par action
            if (!isset($report['by_action'][$action])) {
                $report['by_action'][$action] = 0;
            }
            $report['by_action'][$action]++;
            
            // Compter par résultat
            if (!isset($report['by_result'][$result])) {
                $report['by_result'][$result] = 0;
            }
            $report['by_result'][$result]++;
            
            // Compter par heure
            if (!isset($report['by_hour'][$hour])) {
                $report['by_hour'][$hour] = 0;
            }
            $report['by_hour'][$hour]++;
            
            // Activité récente (dernières 10 actions)
            if (count($report['recent_activity']) < 10) {
                $report['recent_activity'][] = [
                    'time' => $log->created_at->format('H:i:s'),
                    'action' => $log->action_formatted,
                    'result' => $log->result_formatted,
                    'url' => $log->url
                ];
            }
            
            // Activité suspecte
            if ($log->isSuspicious()) {
                $report['suspicious_activity'][] = [
                    'time' => $log->created_at->format('Y-m-d H:i:s'),
                    'action' => $log->action_formatted,
                    'result' => $log->result_formatted,
                    'ip_address' => $log->ip_address,
                    'reason' => $this->getSuspiciousReason($log)
                ];
            }
        }

        // Trier les tableaux
        arsort($report['by_action']);
        arsort($report['by_result']);
        ksort($report['by_hour']);

        return $report;
    }

    public function generateSecurityReport(): array
    {
        $report = [
            'period' => [
                'start' => Carbon::now()->subDay(),
                'end' => Carbon::now()
            ],
            'failed_logins' => $this->getFailedLoginAttempts(24),
            'suspicious_ips' => [],
            'unusual_activity' => [],
            'recommendations' => []
        ];

        // Identifier les IPs suspectes
        $suspiciousIps = $this->model->select('ip_address', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->where(function ($query) {
                $query->where('result', AccessLog::RESULT_FAILED)
                      ->orWhere('result', AccessLog::RESULT_DENIED);
            })
            ->groupBy('ip_address')
            ->having('count', '>', 5)
            ->orderBy('count', 'desc')
            ->get();

        foreach ($suspiciousIps as $ip) {
            $report['suspicious_ips'][] = [
                'ip_address' => $ip->ip_address,
                'attempts' => $ip->count,
                'country' => $this->getIpCountry($ip->ip_address),
                'recommendation' => $ip->count > 10 ? 'Bloquer cette IP' : 'Surveiller cette IP'
            ];
        }

        // Activité inhabituelle
        $unusualHours = $this->model->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->having('hour', '>=', 22)
            ->orHaving('hour', '<=', 5)
            ->orderBy('count', 'desc')
            ->get();

        foreach ($unusualHours as $hour) {
            if ($hour->count > 10) {
                $report['unusual_activity'][] = [
                    'hour' => $hour->hour,
                    'activity_count' => $hour->count,
                    'description' => "Activité élevée pendant les heures non ouvrables ({$hour->hour}h)"
                ];
            }
        }

        // Recommandations
        if (count($report['suspicious_ips']) > 0) {
            $report['recommendations'][] = 'Considérer le blocage des IPs suspectes';
        }
        
        if (count($report['failed_logins']) > 20) {
            $report['recommendations'][] = 'Renforcer les politiques de mot de passe';
        }
        
        if (count($report['unusual_activity']) > 0) {
            $report['recommendations'][] = 'Surveiller l\'activité pendant les heures non ouvrables';
        }

        return $report;
    }

    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        // Compter avant suppression
        $countToDelete = $this->model->where('created_at', '<', $cutoffDate)->count();
        
        // Supprimer en lots pour éviter les timeouts
        $deleted = 0;
        $batchSize = 1000;
        
        do {
            $deletedInBatch = $this->model->where('created_at', '<', $cutoffDate)
                ->limit($batchSize)
                ->delete();
            
            $deleted += $deletedInBatch;
            
        } while ($deletedInBatch > 0);

        $this->clearAccessLogCache();

        return $deleted;
    }

    /**
     * Obtenir le pays d'une IP
     */
    private function getIpCountry(string $ip): ?string
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }

        // Utiliser un service de géolocalisation (à adapter selon vos besoins)
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country");
            
            if ($response) {
                $data = json_decode($response, true);
                if ($data['status'] === 'success') {
                    return $data['country'] ?? 'Inconnu';
                }
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de géolocalisation
        }

        return 'Inconnu';
    }

    /**
     * Obtenir la raison d'une activité suspecte
     */
    private function getSuspiciousReason(AccessLog $log): string
    {
        if ($log->result === AccessLog::RESULT_DENIED) {
            return 'Accès refusé';
        }
        
        if ($log->result === AccessLog::RESULT_FAILED) {
            return 'Échec d\'authentification';
        }
        
        if ($log->response_code >= 400) {
            return 'Erreur HTTP ' . $log->response_code;
        }
        
        return 'Activité inhabituelle';
    }

    /**
     * Analyser les modèles d'accès
     */
    public function analyzeAccessPatterns(): array
    {
        $patterns = [
            'peak_hours' => [],
            'common_actions' => [],
            'user_activity' => [],
            'geographic_distribution' => []
        ];

        // Heures de pointe
        $peakHours = $this->model->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        foreach ($peakHours as $hour) {
            $patterns['peak_hours'][] = [
                'hour' => $hour->hour,
                'activity_count' => $hour->count
            ];
        }

        // Actions courantes
        $commonActions = $this->model->select('action', DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        foreach ($commonActions as $action) {
            $patterns['common_actions'][] = [
                'action' => $action->action,
                'count' => $action->count
            ];
        }

        // Utilisateurs les plus actifs
        $activeUsers = $this->model->select('user_id', DB::raw('count(*) as count'))
            ->with('user')
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        foreach ($activeUsers as $user) {
            $patterns['user_activity'][] = [
                'user' => $user->user->name ?? 'Inconnu',
                'activity_count' => $user->count
            ];
        }

        return $patterns;
    }

    /**
     * Vider le cache des logs d'accès
     */
    private function clearAccessLogCache(): void
    {
        Cache::forget('access_logs.all');
        Cache::forget('access_logs.statistics');
        Cache::forget('access_logs.suspicious');
        
        // Nettoyer les caches par action
        $actions = [
            AccessLog::TYPE_LOGIN, AccessLog::TYPE_LOGOUT, AccessLog::TYPE_VIEW,
            AccessLog::TYPE_CREATE, AccessLog::TYPE_UPDATE, AccessLog::TYPE_DELETE,
            AccessLog::TYPE_BACKUP, AccessLog::TYPE_RESTORE, AccessLog::TYPE_EXPORT
        ];
        
        foreach ($actions as $action) {
            Cache::forget("access_logs.action.{$action}");
        }
        
        // Nettoyer les caches par résultat
        $results = [AccessLog::RESULT_SUCCESS, AccessLog::RESULT_FAILED, AccessLog::RESULT_DENIED];
        foreach ($results as $result) {
            Cache::forget("access_logs.result.{$result}");
        }
        
        // Nettoyer les caches par période
        for ($i = 1; $i <= 24; $i++) {
            Cache::forget("access_logs.failed_logins.{$i}");
        }
    }
}