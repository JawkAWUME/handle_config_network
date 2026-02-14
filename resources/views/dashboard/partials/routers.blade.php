<div class="fade-in">
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" x-model="filters.routers.search" @input.debounce="filterRouters" placeholder="Rechercher un routeur...">
        </div>
        <div class="filter-group">
            <select class="filter-select" x-model="filters.routers.status" @change="filterRouters">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
            <select class="filter-select" x-model="filters.routers.site" @change="filterRouters">
                <option value="">Tous les sites</option>
                <template x-for="site in sites" :key="site.id">
                    <option :value="site.name" x-text="site.name"></option>
                </template>
            </select>
        </div>
    </div>

    <section class="equipment-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-route"></i> Gestion des routeurs
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info" x-text="routers.length + ' routeur(s)'"></span>
                @can('create', App\Models\Router::class)
                <button class="btn btn-primary" @click="openCreateModal('router')">
                    <i class="fas fa-plus"></i> Nouveau routeur
                </button>
                @endcan
                <a href="{{ route('routers.export') }}" class="btn btn-outline">
                    <i class="fas fa-download"></i> Exporter
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th>Nom / Modèle</th>
                        <th>Site</th>
                        <th>IP NMS / SERVICE</th>
                        <th>Interfaces</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="rt in filteredRouters" :key="rt.id">
                        <tr>
                            <td>
                                <strong x-text="rt.name"></strong><br>
                                <small class="text-muted" x-text="rt.model"></small>
                            </td>
                            <td x-text="rt.site"></td>
                            <td>
                                <div><code x-text="rt.ip_nms || 'N/A'"></code> (VLAN <span x-text="rt.vlan_nms || 'N/A'"></span>)</div>
                                <div><code x-text="rt.ip_service || 'N/A'"></code> (VLAN <span x-text="rt.vlan_service || 'N/A'"></span>)</div>
                            </td>
                            <td>
                                <span class="status-badge status-info" x-text="rt.interfaces_up_count + '/' + rt.interfaces_count"></span>
                            </td>
                            <td>
                                <span class="status-badge" 
                                      :class="{ 'status-active': rt.status, 'status-danger': !rt.status }"
                                      x-text="rt.status ? 'Actif' : 'Inactif'">
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Voir" @click="viewItem('routers', rt.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Tester" @click="testConnectivity('router', rt.id)">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Mettre à jour les interfaces" @click="updateInterfaces(rt.id)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    @can('delete', App\Models\Router::class)
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer" @click="deleteItem('routers', rt.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredRouters.length === 0">
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-route fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun routeur trouvé</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>