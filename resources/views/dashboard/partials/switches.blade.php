{{--
    dashboard/partials/switches.blade.php
    ─────────────────────────────────────
    CORRECTIONS appliquées :
    ✅ Plus d'@include de switches-modals (modaux centralisés dans modals.blade.php)
    ✅ viewItem() utilisé à la place de viewEquipmentDetails() → alimente equipmentDetailsModal
    ✅ Filtres status comparent des strings (active|warning|danger)
--}}
<div class="fade-in">

    {{-- ── Filtres ─────────────────────────────────────────────────── --}}
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text"
                   x-model="filters.switches.search"
                   placeholder="Rechercher un switch…">
        </div>
        <div class="filter-group">
            <select class="filter-select" x-model="filters.switches.status">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="warning">Avertissement</option>
                <option value="danger">Critique</option>
            </select>
            <select class="filter-select" x-model="filters.switches.site">
                <option value="">Tous les sites</option>
                <template x-for="site in sites" :key="site.id">
                    <option :value="site.name" x-text="site.name"></option>
                </template>
            </select>
        </div>
    </div>

    {{-- ── Section table ───────────────────────────────────────────── --}}
    <section class="equipment-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-exchange-alt"></i> Gestion des switchs
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info"
                      x-text="filteredSwitches.length + ' switch(s)'"></span>

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
                        <th>Dernier accès</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="sw in filteredSwitches" :key="sw.id">
                        <tr>
                            <td>
                                <strong x-text="sw.name"></strong><br>
                                <small class="text-muted" x-text="sw.model || 'N/A'"></small><br>
                                <small class="text-muted" x-text="sw.brand || ''"></small>
                            </td>

                            <td x-text="sw.site || 'N/A'"></td>

                            <td>
                                <div>
                                    <code x-text="sw.ip_nms || 'N/A'"></code>
                                    (VLAN <span x-text="sw.vlan_nms || 'N/A'"></span>)
                                </div>
                                <div>
                                    <code x-text="sw.ip_service || 'N/A'"></code>
                                    (VLAN <span x-text="sw.vlan_service || 'N/A'"></span>)
                                </div>
                            </td>

                            <td>
                                <div x-text="sw.ports || 'N/A'"></div>
                                <small><span x-text="sw.vlans || 0"></span> VLAN(s)</small>
                            </td>

                            <td>
                                <div style="font-size:.85rem">
                                    <i class="fas fa-user" style="color:var(--accent-color)"></i>
                                    <span x-text="sw.last_access_user || 'Aucun accès'"></span>
                                </div>
                                <div style="font-size:.8rem;color:var(--text-light)">
                                    <span x-text="formatDate(sw.last_access_time)"></span>
                                </div>
                            </td>

                            {{-- status est une STRING (active|warning|danger) --}}
                            <td>
                                <span class="status-badge"
                                      :class="{
                                          'status-active':  sw.status === 'active',
                                          'status-warning': sw.status === 'warning',
                                          'status-danger':  sw.status === 'danger'
                                      }"
                                      x-text="sw.status === 'active'  ? 'Actif'
                                             : sw.status === 'warning' ? 'Avertissement'
                                             : 'Critique'">
                                </span>
                            </td>

                            <td>
                                <div class="action-buttons">
                                    {{-- viewItem() → ouvre equipmentDetailsModal --}}
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Voir"
                                            @click="viewItem('switches', sw.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Configurer les ports"
                                            @click="configurePorts(sw.id)">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    @can('delete', App\Models\SwitchModel::class)
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Supprimer"
                                            @click="deleteItem('switches', sw.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredSwitches.length === 0">
                        <td colspan="7"
                            style="padding:40px;text-align:center;color:var(--text-light)">
                            <i class="fas fa-exchange-alt fa-3x"
                               style="opacity:.3;display:block;margin-bottom:12px"></i>
                            Aucun switch trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>