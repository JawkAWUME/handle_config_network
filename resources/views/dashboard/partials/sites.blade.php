<div class="fade-in">
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" x-model="filters.sites.search" placeholder="Rechercher un site..." autocomplete="off">
        </div>
    </div>

    <section class="equipment-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-building"></i> Gestion des sites
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info" x-text="sites.length + ' site(s)'"></span>
                @can('create', App\Models\Site::class)
                <button class="btn btn-primary" @click="openCreateModal('site')">
                    <i class="fas fa-plus"></i> Nouveau site
                </button>
                @endcan
                <a href="{{ route('sites.export') }}" class="btn btn-outline">
                    <i class="fas fa-download"></i> Exporter
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th>Nom / Code</th>
                        <th>Adresse</th>
                        <th>Équipements</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="site in filteredSites" :key="site.id">
                        <tr>
                            <td>
                                <strong x-text="site.name"></strong><br>
                                <small class="text-muted" x-text="site.city"></small>
                            </td>
                            <td>
                                <div x-text="site.address || 'N/A'"></div>
                                <small class="text-muted">
                                    <span x-text="site.postal_code || ''"></span>
                                    <span x-text="site.city || ''"></span>
                                    <span x-show="site.country" x-text="', ' + site.country"></span>
                                </small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 12px; font-size: 0.85rem;">
                                    <span class="status-badge status-danger" style="font-size: 0.75rem; cursor: pointer;" 
                                          @click="showSiteEquipment(site.id, 'firewall')">
                                        <i class="fas fa-fire"></i> <span x-text="(site.firewalls_count || 0)"></span>
                                    </span>
                                    <span class="status-badge status-info" style="font-size: 0.75rem; cursor: pointer;" 
                                          @click="showSiteEquipment(site.id, 'router')">
                                        <i class="fas fa-route"></i> <span x-text="(site.routers_count || 0)"></span>
                                    </span>
                                    <span class="status-badge status-active" style="font-size: 0.75rem; cursor: pointer;" 
                                          @click="showSiteEquipment(site.id, 'switch')">
                                        <i class="fas fa-exchange-alt"></i> <span x-text="(site.switches_count || 0)"></span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <div x-show="site.contact_name">
                                        <i class="fas fa-user" style="width: 14px;"></i>
                                        <span x-text="site.contact_name"></span>
                                    </div>
                                    <div x-show="site.contact_email">
                                        <i class="fas fa-envelope" style="width: 14px;"></i>
                                        <span x-text="site.contact_email"></span>
                                    </div>
                                    <div x-show="site.contact_phone">
                                        <i class="fas fa-phone" style="width: 14px;"></i>
                                        <span x-text="site.contact_phone"></span>
                                    </div>
                                    <div x-show="!site.contact_name && !site.contact_email && !site.contact_phone" style="color: var(--text-light);">
                                        Aucun contact
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Voir" @click="viewItem('sites', site.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @can('updateAny', App\Models\Site::class)
                                    <button class="btn btn-outline btn-sm btn-icon" title="Modifier" @click="editItem('sites', site.id)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('deleteAny', App\Models\Site::class)
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer" @click="deleteItem('sites', site.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredSites.length === 0">
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun site trouvé</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

