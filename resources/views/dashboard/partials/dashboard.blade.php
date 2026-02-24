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

    <!-- Graphiques (chart.js) -->
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

    <!-- Section Activité Récente avec credentials -->
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
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            <!-- Derniers Firewalls -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-fire"></i> Derniers Firewalls
                </h4>
                <div style="max-height: 350px; overflow-y: auto;">
                    <template x-if="firewalls && firewalls.length > 0">
                        <div>
                            <template x-for="firewall in firewalls.slice(0, 3)" :key="firewall.id">
                                <div style="padding: 12px; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s; border-radius: 8px; margin-bottom: 12px; background: white;" 
                                     @click="viewEquipmentDetails('firewall', firewall)"
                                     @mouseenter="$el.style.borderColor = 'var(--primary-color)'; $el.style.boxShadow = 'var(--card-shadow)'; $el.style.transform = 'translateY(-2px)'"
                                     @mouseleave="$el.style.borderColor = 'var(--border-color)'; $el.style.boxShadow = 'none'; $el.style.transform = 'translateY(0)'">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <strong x-text="firewall.name" style="color: var(--text-color); font-size: 1rem;"></strong>
                                        <span class="status-badge" 
                                              :class="firewall.status ? 'status-active' : 'status-danger'" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="firewall.status ? 'Actif' : 'Inactif'"></span>
                                        </span>
                                    </div>
                                    
                                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 8px;">
                                        <div style="margin-bottom: 2px;">
                                            <i class="fas fa-building" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="firewall.site?.name || firewall.site || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-microchip" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="firewall.model || 'N/A'"></span>
                                        </div>
                                    </div>
                                    
                                    <div style="height: 1px; background: var(--border-color); margin: 8px 0;"></div>
                                    
                                    <div style="display: grid; gap: 4px; font-size: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-user-shield" style="color: var(--accent-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Compte:</span>
                                            <span x-text="firewall.username || 'Non configuré'" style="font-family: monospace; color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-sign-in-alt" style="color: var(--success-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Dernier accès:</span>
                                            <span x-text="getLastAccessUser(firewall)" style="color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-clock" style="color: var(--info-color); width: 16px;"></i>
                                            <span x-text="formatDate(firewall.updated_at)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!firewalls || firewalls.length === 0">
                        <p class="text-muted text-center" style="padding: 20px; color: var(--text-light);">
                            <i class="fas fa-info-circle"></i> Aucun firewall récent
                        </p>
                    </template>
                </div>
            </div>
            
            <!-- Derniers Routeurs -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-route"></i> Derniers Routeurs
                </h4>
                <div style="max-height: 350px; overflow-y: auto;">
                    <template x-if="routers && routers.length > 0">
                        <div>
                            <template x-for="router in routers.slice(0, 3)" :key="router.id">
                                <div style="padding: 12px; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s; border-radius: 8px; margin-bottom: 12px; background: white;" 
                                     @click="viewEquipmentDetails('router', router)"
                                     @mouseenter="$el.style.borderColor = 'var(--primary-color)'; $el.style.boxShadow = 'var(--card-shadow)'; $el.style.transform = 'translateY(-2px)'"
                                     @mouseleave="$el.style.borderColor = 'var(--border-color)'; $el.style.boxShadow = 'none'; $el.style.transform = 'translateY(0)'">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <strong x-text="router.name" style="color: var(--text-color); font-size: 1rem;"></strong>
                                        <span class="status-badge" 
                                              :class="router.status ? 'status-active' : 'status-danger'" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="router.status ? 'Actif' : 'Inactif'"></span>
                                        </span>
                                    </div>
                                    
                                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 8px;">
                                        <div style="margin-bottom: 2px;">
                                            <i class="fas fa-building" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="router.site?.name || router.site || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-microchip" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="router.model || 'N/A'"></span>
                                        </div>
                                    </div>
                                    
                                    <div style="height: 1px; background: var(--border-color); margin: 8px 0;"></div>
                                    
                                    <div style="display: grid; gap: 4px; font-size: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-user-shield" style="color: var(--accent-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Compte:</span>
                                            <span x-text="router.username || 'Non configuré'" style="font-family: monospace; color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-sign-in-alt" style="color: var(--success-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Dernier accès:</span>
                                            <span x-text="getLastAccessUser(router)" style="color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-clock" style="color: var(--info-color); width: 16px;"></i>
                                            <span x-text="formatDate(router.updated_at)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!routers || routers.length === 0">
                        <p class="text-muted text-center" style="padding: 20px; color: var(--text-light);">
                            <i class="fas fa-info-circle"></i> Aucun routeur récent
                        </p>
                    </template>
                </div>
            </div>
            
            <!-- Derniers Switchs -->
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                    <i class="fas fa-exchange-alt"></i> Derniers Switchs
                </h4>
                <div style="max-height: 350px; overflow-y: auto;">
                    <template x-if="switches && switches.length > 0">
                        <div>
                            <template x-for="sw in switches.slice(0, 3)" :key="sw.id">
                                <div style="padding: 12px; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s; border-radius: 8px; margin-bottom: 12px; background: white;" 
                                     @click="viewEquipmentDetails('switch', sw)"
                                     @mouseenter="$el.style.borderColor = 'var(--primary-color)'; $el.style.boxShadow = 'var(--card-shadow)'; $el.style.transform = 'translateY(-2px)'"
                                     @mouseleave="$el.style.borderColor = 'var(--border-color)'; $el.style.boxShadow = 'none'; $el.style.transform = 'translateY(0)'">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <strong x-text="sw.name" style="color: var(--text-color); font-size: 1rem;"></strong>
                                        <!-- ✅ CORRECTION : status est un BOOLEAN, pas une string -->
                                        <span class="status-badge" 
                                              :class="sw.status ? 'status-active' : 'status-danger'" 
                                              style="font-size: 0.7rem;">
                                            <span x-text="sw.status ? 'Actif' : 'Inactif'"></span>
                                        </span>
                                    </div>
                                    
                                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 8px;">
                                        <div style="margin-bottom: 2px;">
                                            <i class="fas fa-building" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="sw.site?.name || sw.site || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-microchip" style="width: 14px; color: var(--primary-color);"></i>
                                            <span x-text="sw.model || 'N/A'"></span>
                                        </div>
                                    </div>
                                    
                                    <div style="height: 1px; background: var(--border-color); margin: 8px 0;"></div>
                                    
                                    <div style="display: grid; gap: 4px; font-size: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-user-shield" style="color: var(--accent-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Compte:</span>
                                            <span x-text="sw.username || 'Non configuré'" style="font-family: monospace; color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-sign-in-alt" style="color: var(--success-color); width: 16px;"></i>
                                            <span style="font-weight: 500;">Dernier accès:</span>
                                            <span x-text="sw.last_access_user || 'Jamais'" style="color: var(--text-color);"></span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light);">
                                            <i class="fas fa-clock" style="color: var(--info-color); width: 16px;"></i>
                                            <span x-text="formatDate(sw.last_access_time)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!switches || switches.length === 0">
                        <p class="text-muted text-center" style="padding: 20px; color: var(--text-light);">
                            <i class="fas fa-info-circle"></i> Aucun switch récent
                        </p>
                    </template>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de détails d'équipement -->
<div id="viewEquipmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--border-radius-lg); width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: var(--card-shadow-hover);">
        <!-- Header du modal -->
        <div style="padding: 24px; border-bottom: 2px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas" :class="getEquipmentIcon(modalData.type)"></i>
                <span x-text="modalData.item?.name || 'Détails de l\'équipement'"></span>
            </h3>
            <button @click="closeModal('viewEquipmentModal')" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Contenu du modal -->
        <div style="padding: 24px;" x-html="renderEquipmentDetails()"></div>
        
        <!-- Footer du modal -->
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px; background: #f8fafc;">
            <button class="btn btn-outline" @click="closeModal('equipmentDetailsModal')">
                <i class="fas fa-times"></i> Fermer
            </button>
            <button class="btn btn-primary" @click="editEquipment(modalData.type, modalData.item.id)" x-show="permissions.create">
                <i class="fas fa-edit"></i> Modifier
            </button>
        </div>
    </div>
</div>
</div>