<div class="fade-in">
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" x-model="filters.switches.search" @input.debounce="filterSwitches" placeholder="Rechercher un switch...">
        </div>
        <div class="filter-group">
            <select class="filter-select" x-model="filters.switches.status" @change="filterSwitches">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="warning">Avertissement</option>
                <option value="danger">Critique</option>
            </select>
            <select class="filter-select" x-model="filters.switches.site" @change="filterSwitches">
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
                <i class="fas fa-exchange-alt"></i> Gestion des switchs
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info" x-text="switches.length + ' switch(s)'"></span>
                @can('create', App\Models\SwitchModel::class)
                <button class="btn btn-primary" @click="openCreateModal('switch')">
                    <i class="fas fa-plus"></i> Nouveau switch
                </button>
                @endcan
                <a href="{{ route('api.switches.export') }}" class="btn btn-outline">
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
                        <th>Ports / VLANs</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="sw in filteredSwitches" :key="sw.id">
                        <tr>
                            <td>
                                <strong x-text="sw.name"></strong><br>
                                <small class="text-muted" x-text="sw.model"></small>
                            </td>
                            <td x-text="sw.site"></td>
                            <td>
                                <div><code x-text="sw.ip_nms || 'N/A'"></code> (VLAN <span x-text="sw.vlan_nms || 'N/A'"></span>)</div>
                                <div><code x-text="sw.ip_service || 'N/A'"></code> (VLAN <span x-text="sw.vlan_service || 'N/A'"></span>)</div>
                            </td>
                            <td>
                                <div x-text="sw.ports || 'N/A'"></div>
                                <small><span x-text="sw.vlans || 0"></span> VLANs</small>
                            </td>
                            <td>
                                <span class="status-badge" 
                                      :class="{
                                          'status-active': sw.status === 'active',
                                          'status-warning': sw.status === 'warning',
                                          'status-danger': sw.status === 'danger'
                                      }"
                                      x-text="sw.status === 'active' ? 'Actif' : (sw.status === 'warning' ? 'Avertissement' : 'Critique')">
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Voir" @click="viewItem('switches', sw.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Tester" @click="testConnectivity('switch', sw.id)">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Configurer les ports" @click="configurePorts(sw.id)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    @can('delete', App\Models\SwitchModel::class)
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer" @click="deleteItem('switches', sw.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredSwitches.length === 0">
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun switch trouvé</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>