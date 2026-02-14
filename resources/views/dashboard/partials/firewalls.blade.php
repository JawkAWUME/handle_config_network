<div class="fade-in">
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" x-model="filters.firewalls.search" @input.debounce="filterFirewalls" placeholder="Rechercher un firewall...">
        </div>
        <div class="filter-group">
            <select class="filter-select" x-model="filters.firewalls.status" @change="filterFirewalls">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
            <select class="filter-select" x-model="filters.firewalls.site" @change="filterFirewalls">
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
                <i class="fas fa-fire"></i> Gestion des firewalls
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info" x-text="firewalls.length + ' firewall(s)'"></span>
                @can('create', App\Models\Firewall::class)
                <button class="btn btn-primary" @click="openCreateModal('firewall')">
                    <i class="fas fa-plus"></i> Nouveau firewall
                </button>
                @endcan
                <a href="{{ route('firewalls.export') }}" class="btn btn-outline">
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
                        <th>Statut</th>
                        <th>Politiques</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="fw in filteredFirewalls" :key="fw.id">
                        <tr>
                            <td>
                                <strong x-text="fw.name"></strong><br>
                                <small class="text-muted" x-text="fw.model"></small>
                            </td>
                            <td x-text="fw.site"></td>
                            <td>
                                <div><code x-text="fw.ip_nms || 'N/A'"></code> (VLAN <span x-text="fw.vlan_nms || 'N/A'"></span>)</div>
                                <div><code x-text="fw.ip_service || 'N/A'"></code> (VLAN <span x-text="fw.vlan_service || 'N/A'"></span>)</div>
                            </td>
                            <td>
                                <span class="status-badge" 
                                      :class="{ 'status-active': fw.status, 'status-danger': !fw.status }"
                                      x-text="fw.status ? 'Actif' : 'Inactif'">
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-info" x-text="fw.security_policies_count + ' règles'"></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Voir" @click="viewItem('firewalls', fw.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Tester" @click="testConnectivity('firewall', fw.id)">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Politiques de sécurité" @click="updateSecurityPolicies(fw.id)">
                                        <i class="fas fa-shield-alt"></i>
                                    </button>
                                    @can('delete', App\Models\Firewall::class)
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer" @click="deleteItem('firewalls', fw.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredFirewalls.length === 0">
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun firewall trouvé</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>