<?php

namespace App\Services;

use App\Repositories\AccessLogRepository;
use App\Events\SecurityAccessLogged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use Carbon\Carbon;

class AccessLogService
{
    protected const SUSPICIOUS_THRESHOLDS = [
        'failures' => 5,
        'time_window' => 15,
        'distance_km' => 1000,
    ];

    public function __construct(
        protected AccessLogRepository $repository
    ) {}

    public function logAccess(string $action, array $context = []): \App\Models\AccessLog
    {
        try {
            $data = $this->prepareData($action, $context);
            $enriched = $this->enrichData($data);
            $security = $this->analyzeSecurity($enriched);
            
            $log = $this->repository->create(array_merge($enriched, [
                'is_suspicious' => $security['is_suspicious'],
                'risk_level' => $security['risk_level'],
                'security_flags' => $security['flags'],
            ]));
            
            $this->triggerEvents($log, $security);
            
            Log::debug('[AccessLog] Log created', [
                'id' => $log->id,
                'action' => $action,
                'user' => $log->user_id,
                'suspicious' => $log->is_suspicious,
            ]);
            
            return $log;
        } catch (Exception $e) {
            Log::error('[AccessLog] Logging failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getLogs(array $filters = [], bool $paginate = true)
    {
        try {
            $query = $this->repository->query()
                ->with(['user:id,name,email'])
                ->latest();
            
            $this->applyFilters($query, $filters);
            
            if (!empty($filters['search'])) {
                $this->applySearch($query, $filters['search']);
            }
            
            if ($paginate) {
                return $query->paginate($filters['per_page'] ?? 20)
                    ->through(fn($log) => $this->formatForDisplay($log));
            }
            
            return $query->get()->map(fn($log) => $this->formatForDisplay($log));
        } catch (Exception $e) {
            Log::error('[AccessLog] Fetch failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSecurityReport(array $options = []): array
    {
        try {
            $start = Carbon::parse($options['start_date'] ?? now()->subDays(30))->startOfDay();
            $end = Carbon::parse($options['end_date'] ?? now())->endOfDay();
            
            return Cache::remember("security_report_{$start->timestamp}_{$end->timestamp}", 300, 
                fn() => $this->generateReport($start, $end));
        } catch (Exception $e) {
            Log::error('[AccessLog] Report failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function detectBruteForce(string $ipAddress, ?int $userId = null): array
    {
        try {
            $windowStart = now()->subMinutes(self::SUSPICIOUS_THRESHOLDS['time_window']);
            
            $attempts = $this->repository->query()
                ->where('ip_address', $ipAddress)
                ->where('created_at', '>=', $windowStart)
                ->whereIn('action', ['login', 'authentication'])
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->get();
            
            $failed = $attempts->where('result', 'failed');
            $isBruteForce = $failed->count() >= self::SUSPICIOUS_THRESHOLDS['failures'];
            
            if ($isBruteForce) {
                Log::warning('[AccessLog] Brute force detected', ['ip' => $ipAddress]);
            }
            
            return [
                'ip_address' => $ipAddress,
                'user_id' => $userId,
                'attempts' => [
                    'total' => $attempts->count(),
                    'failed' => $failed->count(),
                    'successful' => $attempts->where('result', 'success')->count(),
                ],
                'is_brute_force' => $isBruteForce,
                'recommendation' => $isBruteForce 
                    ? 'Block IP temporarily' 
                    : 'Monitor activity',
            ];
        } catch (Exception $e) {
            Log::error('[AccessLog] Brute force detection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function prepareData(string $action, array $context): array
    {
        $user = auth()->user();
        
        return [
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'action' => $action,
            'parameters' => $this->sanitizeParams($context['parameters'] ?? []),
            'result' => $context['result'] ?? 'success',
            'response_code' => $context['response_code'] ?? 200,
            'response_time' => $this->calculateResponseTime($context['start_time'] ?? LARAVEL_START),
            'error_message' => $context['error_message'] ?? null,
        ];
    }

    private function enrichData(array $data): array
    {
        $geo = $this->getGeoLocation($data['ip_address']);
        $browser = $this->parseUserAgent($data['user_agent']);
        
        return array_merge($data, $geo, $browser, [
            'is_bot' => $this->isBot($data['user_agent']),
            'is_mobile' => str_contains($data['user_agent'], 'Mobile'),
        ]);
    }

    private function analyzeSecurity(array $data): array
    {
        $flags = [];
        $riskLevel = 'low';
        
        if ($data['action'] === 'login' && $data['result'] === 'failed') {
            $recentFailures = $this->countRecentFailures($data['ip_address'], $data['user_id'] ?? null);
            if ($recentFailures >= 3) {
                $flags[] = 'multiple_login_failures';
                $riskLevel = 'high';
            }
        }
        
        if ($data['result'] === 'denied') {
            $flags[] = 'access_denied';
            $riskLevel = 'high';
        }
        
        if ($this->isUnusualTime($data['created_at'] ?? now())) {
            $flags[] = 'unusual_time';
            $riskLevel = max($riskLevel, 'medium');
        }
        
        if ($data['is_bot'] ?? false) {
            $flags[] = 'bot_detected';
            $riskLevel = max($riskLevel, 'high');
        }
        
        return [
            'is_suspicious' => !empty($flags),
            'risk_level' => $riskLevel,
            'flags' => $flags,
            'score' => count($flags) * 20,
        ];
    }

    private function triggerEvents($log, array $security): void
    {
        if ($security['is_suspicious'] || $log->result === 'denied') {
            event(new SecurityAccessLogged($log, [
                'userId' => $log->user_id,
                'ipAddress' => $log->ip_address,
                'riskLevel' => $security['risk_level'],
            ]));
        }
    }

    private function sanitizeParams(array $params): array
    {
        $sensitive = ['password', 'token', 'secret', 'key', 'api_key'];
        
        foreach ($params as $key => $value) {
            foreach ($sensitive as $field) {
                if (stripos($key, $field) !== false) {
                    $params[$key] = '***MASKED***';
                    break;
                }
            }
        }
        
        return $params;
    }

    private function calculateResponseTime(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    private function getGeoLocation(string $ip): array
    {
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return ['country' => 'Local', 'city' => 'Localhost'];
        }
        
        return Cache::remember("geo_{$ip}", 86400, function () use ($ip) {
            try {
                $url = "http://ip-api.com/json/{$ip}?fields=status,country,city";
                $response = @file_get_contents($url);
                
                if ($response) {
                    $data = json_decode($response, true);
                    if ($data['status'] === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[AccessLog] Geolocation failed: ' . $e->getMessage());
            }
            
            return [];
        });
    }

    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $platform = 'Unknown';
        
        if (str_contains($ua, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
        elseif (str_contains($ua, 'Edge')) $browser = 'Edge';
        
        if (str_contains($ua, 'Windows')) $platform = 'Windows';
        elseif (str_contains($ua, 'Mac')) $platform = 'macOS';
        elseif (str_contains($ua, 'Linux')) $platform = 'Linux';
        elseif (str_contains($ua, 'Android')) $platform = 'Android';
        elseif (str_contains($ua, 'iOS')) $platform = 'iOS';
        
        return ['browser' => $browser, 'platform' => $platform];
    }

    private function isBot(string $ua): bool
    {
        $patterns = ['bot', 'crawler', 'spider', 'curl', 'python', 'java'];
        $ua = strtolower($ua);
        
        foreach ($patterns as $pattern) {
            if (str_contains($ua, $pattern)) return true;
        }
        
        return false;
    }

    private function countRecentFailures(string $ip, ?int $userId = null): int
    {
        return $this->repository->query()
            ->where('ip_address', $ip)
            ->where('action', 'login')
            ->where('result', 'failed')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();
    }

    private function isUnusualTime(Carbon $time): bool
    {
        $hour = $time->hour;
        return $time->isWeekend() || $hour < 8 || $hour > 18;
    }

    private function applyFilters($query, array $filters): void
    {
        $mappings = [
            'user_id' => 'user_id',
            'ip_address' => 'ip_address',
            'action' => 'action',
            'result' => 'result',
            'country' => 'country',
            'suspicious' => 'is_suspicious',
        ];
        
        foreach ($mappings as $key => $column) {
            if (!empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date']));
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }
    }

    private function applySearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('url', 'like', "%{$search}%")
              ->orWhere('user_agent', 'like', "%{$search}%")
              ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                                               ->orWhere('email', 'like', "%{$search}%"));
        });
    }

    private function formatForDisplay($log): array
    {
        return [
            'id' => $log->id,
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
            ] : null,
            'action' => $log->action,
            'result' => $log->result,
            'is_suspicious' => $log->is_suspicious,
            'risk_level' => $log->risk_level,
            'ip_address' => $log->ip_address,
            'location' => $log->city && $log->country ? "{$log->city}, {$log->country}" : 'Unknown',
            'browser' => $log->browser,
            'platform' => $log->platform,
            'url' => $log->url,
            'response_code' => $log->response_code,
            'response_time' => $log->response_time,
            'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
            'security_flags' => $log->security_flags ?? [],
        ];
    }

    private function generateReport(Carbon $start, Carbon $end): array
    {
        $logs = $this->repository->getBetween($start, $end);
        
        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'days' => $start->diffInDays($end),
            ],
            'summary' => [
                'total_logs' => $logs->count(),
                'unique_users' => $logs->groupBy('user_id')->count(),
                'unique_ips' => $logs->groupBy('ip_address')->count(),
                'success_rate' => $logs->where('result', 'success')->count() / max($logs->count(), 1) * 100,
            ],
            'security' => [
                'suspicious' => $logs->where('is_suspicious', true)->count(),
                'failed_logins' => $logs->where('action', 'login')->where('result', 'failed')->count(),
                'access_denied' => $logs->where('result', 'denied')->count(),
            ],
            'top_stats' => [
                'actions' => $logs->groupBy('action')->map->count()->sortDesc()->take(5),
                'users' => $logs->whereNotNull('user_id')->groupBy('user_id')->map->count()->sortDesc()->take(5),
                'ips' => $logs->groupBy('ip_address')->map->count()->sortDesc()->take(5),
                'countries' => $logs->whereNotNull('country')->groupBy('country')->map->count()->sortDesc()->take(5),
            ],
        ];
    }
}