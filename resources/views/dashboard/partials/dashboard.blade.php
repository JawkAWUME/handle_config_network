<div class="fade-in">
    <!-- Section de bienvenue -->
    <section class="welcome-section">
        <div class="welcome-content">
            <div class="welcome-header">
                <div class="welcome-text">
                    <h2 class="welcome-title">Bienvenue, {{ Auth::user()->name }} !</h2>
                    <p class="welcome-subtitle">
                        Plateforme complète de gestion des configurations réseau pour simplifier l'exploitation, 
                        la maintenance et les audits techniques de votre infrastructure.
                    </p>
                    <div class="welcome-stats">
                        <div class="welcome-stat">
                            <div class="stat-value" x-text="totals.sites || 0"></div>
                            <div class="stat-label">Sites Actifs</div>
                        </div>
                        <div class="welcome-stat">
                            <div class="stat-value" x-text="totals.devices || 0"></div>
                            <div class="stat-label">Équipements</div>
                        </div>
                        <div class="welcome-stat">
                            <div class="stat-value" x-text="(totals.availability || 99.7) + '%'"></div>
                            <div class="stat-label">Disponibilité</div>
                        </div>
                        <div class="welcome-stat">
                            <div class="stat-value" x-text="totals.incidentsToday || 0"></div>
                            <div class="stat-label">Incidents</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- KPI Cards -->
    <section class="kpi-section">
        <h2 style="color: var(--header-bg); margin-bottom: 24px; font-size: 1.8rem;">
            <i class="fas fa-tachometer-alt"></i> Indicateurs Clés de Performance
        </h2>
        <div class="kpi-grid">
            <!-- Sites réseau -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-building"></i></div>
                <div class="kpi-value" x-text="totals.sites || 0"></div>
                <div class="kpi-label">Sites réseau</div>
                <div class="kpi-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +0%
                </div>
            </div>
            
            <!-- Firewalls -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-fire"></i></div>
                <div class="kpi-value" x-text="totals.firewalls || 0"></div>
                <div class="kpi-label">Firewalls</div>
                <div class="kpi-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +0%
                </div>
            </div>
            
            <!-- Routeurs -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-route"></i></div>
                <div class="kpi-value" x-text="totals.routers || 0"></div>
                <div class="kpi-label">Routeurs</div>
                <div class="kpi-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +0%
                </div>
            </div>
            
            <!-- Switchs -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="kpi-value" x-text="totals.switches || 0"></div>
                <div class="kpi-label">Switchs</div>
                <div class="kpi-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +0%
                </div>
            </div>
            
            <!-- Disponibilité -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-bolt"></i></div>
                <div class="kpi-value" x-text="(totals.availability || 99.7) + '%'"></div>
                <div class="kpi-label">Disponibilité</div>
                <div class="kpi-trend" :class="(totals.availability || 99.7) > 95 ? 'trend-up' : ''">
                    <i class="fas" :class="(totals.availability || 99.7) > 95 ? 'fa-arrow-up' : 'fa-arrow-minus'"></i> 
                    <span x-text="(totals.availability || 99.7) > 95 ? '+0.1%' : '±0%'"></span>
                </div>
            </div>
            
            <!-- Sécurité -->
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="kpi-value">99.5%</div>
                <div class="kpi-label">Sécurité</div>
                <div class="kpi-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +0.3%
                </div>
            </div>
        </div>
    </section>

    <!-- Graphiques (chart.js) – les IDs sont utilisés par dashboardApp.initCharts() -->
    <section class="charts-section">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Répartition des Équipements</h3>
                <button class="btn btn-outline btn-sm" @click="toggleChartType('deviceDistribution')">
                    <i class="fas fa-exchange-alt"></i> Type
                </button>
            </div>
            <div class="chart-container">
                <canvas id="deviceDistributionChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> Disponibilité Hebdomadaire</h3>
                <button class="btn btn-outline btn-sm" @click="toggleChartType('availability')">
                    <i class="fas fa-exchange-alt"></i> Type
                </button>
            </div>
            <div class="chart-container">
                <canvas id="availabilityChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-exclamation-triangle"></i> Incidents par Type</h3>
                <button class="btn btn-outline btn-sm" @click="toggleChartType('incidents')">
                    <i class="fas fa-exchange-alt"></i> Type
                </button>
            </div>
            <div class="chart-container">
                <canvas id="incidentsChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-server"></i> Charge des Équipements</h3>
                <button class="btn btn-outline btn-sm" @click="toggleChartType('load')">
                    <i class="fas fa-exchange-alt"></i> Type
                </button>
            </div>
            <div class="chart-container">
                <canvas id="loadChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Section Activité Récente -->
    <section class="equipment-section fade-in">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-history"></i> Activité Récente
            </h2>
            <div class="section-actions">
                <button class="btn btn-outline" @click="init()">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <!-- Derniers Firewalls -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-fire"></i> Derniers Firewalls
                </h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <template x-if="firewalls && firewalls.length > 0">
                        <div>
                            <template x-for="firewall in firewalls.slice(0, 3)" :key="firewall.id">
                                <div style="padding: 10px; border-bottom: 1px solid var(--border-color); cursor: pointer;" 
                                     @click="viewItem('firewalls', firewall.id)">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong x-text="firewall.name"></strong>
                                            <div style="font-size: 0.85rem; color: var(--text-light);">
                                                <span x-text="firewall.site"></span> • <span x-text="firewall.model"></span>
                                            </div>
                                        </div>
                                        <span class="status-badge" 
                                              :class="firewall.status ? 'status-active' : 'status-danger'" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="firewall.status ? 'Actif' : 'Inactif'"></span>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                        <i class="fas fa-clock"></i> <span x-text="firewall.lastSeen || 'N/A'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!firewalls || firewalls.length === 0">
                        <p class="text-muted text-center" style="padding: 20px;">Aucun firewall récent</p>
                    </template>
                </div>
            </div>
            
            <!-- Derniers Routeurs -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-route"></i> Derniers Routeurs
                </h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <template x-if="routers && routers.length > 0">
                        <div>
                            <template x-for="router in routers.slice(0, 3)" :key="router.id">
                                <div style="padding: 10px; border-bottom: 1px solid var(--border-color); cursor: pointer;" 
                                     @click="viewItem('routers', router.id)">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong x-text="router.name"></strong>
                                            <div style="font-size: 0.85rem; color: var(--text-light);">
                                                <span x-text="router.site"></span> • <span x-text="router.model"></span>
                                            </div>
                                        </div>
                                        <span class="status-badge" 
                                              :class="router.status ? 'status-active' : 'status-danger'" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="router.status ? 'Actif' : 'Inactif'"></span>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                        <i class="fas fa-clock"></i> <span x-text="router.lastSeen || 'N/A'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!routers || routers.length === 0">
                        <p class="text-muted text-center" style="padding: 20px;">Aucun routeur récent</p>
                    </template>
                </div>
            </div>
            
            <!-- Derniers Switchs -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-exchange-alt"></i> Derniers Switchs
                </h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <template x-if="switches && switches.length > 0">
                        <div>
                            <template x-for="sw in switches.slice(0, 3)" :key="sw.id">
                                <div style="padding: 10px; border-bottom: 1px solid var(--border-color); cursor: pointer;" 
                                     @click="viewItem('switches', sw.id)">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong x-text="sw.name"></strong>
                                            <div style="font-size: 0.85rem; color: var(--text-light);">
                                                <span x-text="sw.site"></span> • <span x-text="sw.model"></span>
                                            </div>
                                        </div>
                                        <span class="status-badge" 
                                              :class="sw.status === 'active' ? 'status-active' : (sw.status === 'warning' ? 'status-warning' : 'status-danger')" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="sw.status === 'active' ? 'Actif' : (sw.status === 'warning' ? 'Avertissement' : 'Critique')"></span>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                        <i class="fas fa-clock"></i> <span x-text="sw.lastSeen || 'N/A'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!switches || switches.length === 0">
                        <p class="text-muted text-center" style="padding: 20px;">Aucun switch récent</p>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>