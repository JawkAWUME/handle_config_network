{{--
    dashboard/partials/firewalls.blade.php
    ────────────────────────────────────────
    CORRECTIONS appliquées :
    ✅ Modal création firewall supprimé d'ici → centralisé dans modals.blade.php
    ✅ viewItem('firewalls', fw.id) → ouvre equipmentDetailsModal (ID unifié)
    ✅ Filtres status comparent des strings
    ✅ Suppression des @input.debounce inutiles (filtres sont des computed getters Alpine)
--}}
<div class="fade-in">

    {{-- ── Filtres ─────────────────────────────────────────────────── --}}
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text"
                   x-model="filters.firewalls.search"
                   placeholder="Rechercher un firewall…">
        </div>
        <div class="filter-group">
            <select class="filter-select" x-model="filters.firewalls.status">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="danger">Inactif</option>
            </select>
            <select class="filter-select" x-model="filters.firewalls.site">
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
                <i class="fas fa-fire"></i> Gestion des firewalls
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info"
                      x-text="filteredFirewalls.length + ' firewall(s)'"></span>

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
                                <small class="text-muted" x-text="fw.model || 'N/A'"></small><br>
                                <small class="text-muted" x-text="fw.brand || ''"></small>
                            </td>

                            <td x-text="fw.site || 'N/A'"></td>

                            <td>
                                <div>
                                    <code x-text="fw.ip_nms || 'N/A'"></code>
                                    (VLAN <span x-text="fw.vlan_nms || 'N/A'"></span>)
                                </div>
                                <div>
                                    <code x-text="fw.ip_service || 'N/A'"></code>
                                    (VLAN <span x-text="fw.vlan_service || 'N/A'"></span>)
                                </div>
                            </td>

                            {{-- status STRING (active|danger) --}}
                            <td>
                                <span class="status-badge"
                                      :class="{
                                          'status-active': fw.status === 'active',
                                          'status-danger': fw.status === 'danger' || fw.status === 'inactive'
                                      }"
                                      x-text="fw.status === 'active' ? 'Actif' : 'Inactif'">
                                </span>
                            </td>

                            <td>
                                <span class="status-badge status-info"
                                      x-text="(fw.security_policies_count || 0) + ' règle(s)'">
                                </span>
                            </td>

                            <td>
                                <div class="action-buttons">
                                    {{-- viewItem() → ouvre equipmentDetailsModal --}}
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Voir"
                                            @click="viewItem('firewalls', fw.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Tester"
                                            @click="testConnectivity('firewall', fw.id)">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Politiques de sécurité"
                                            @click="updateSecurityPolicies(fw.id)">
                                        <i class="fas fa-shield-alt"></i>
                                    </button>
                                    @can('delete', App\Models\Firewall::class)
                                    <button class="btn btn-outline btn-sm btn-icon"
                                            title="Supprimer"
                                            @click="deleteItem('firewalls', fw.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredFirewalls.length === 0">
                        <td colspan="6"
                            style="padding:40px;text-align:center;color:var(--text-light)">
                            <i class="fas fa-fire fa-3x"
                               style="opacity:.3;display:block;margin-bottom:12px"></i>
                            Aucun firewall trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>