<div class="fade-in">
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" x-model="filters.sites.search" @input.debounce="filterSites" placeholder="Rechercher un site...">
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
                                    {{-- Badge Firewalls cliquable --}}
                                    <span class="status-badge status-danger" style="font-size: 0.75rem; cursor: pointer;" 
                                          @click="showSiteEquipment(site.id, 'firewall')">
                                        <i class="fas fa-fire"></i> <span x-text="(site.firewalls_count || 0)"></span>
                                    </span>
                                    {{-- Badge Routeurs cliquable --}}
                                    <span class="status-badge status-info" style="font-size: 0.75rem; cursor: pointer;" 
                                          @click="showSiteEquipment(site.id, 'router')">
                                        <i class="fas fa-route"></i> <span x-text="(site.routers_count || 0)"></span>
                                    </span>
                                    {{-- Badge Switchs cliquable --}}
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

{{-- ════════════════════════════════════════════════════════════
     MODAL : Création / Édition d'un Site (inchangé)
     ════════════════════════════════════════════════════════════ --}}
<div id="createEquipmentModal"
     x-show="currentModal === 'create' && modalData.type === 'site'"
     x-cloak
     style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 860px; max-height: 92vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- Header --}}
        <div style="padding: 24px; display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                    color: white; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-building"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('createEquipmentModal')"
                    style="background: rgba(255,255,255,0.2); border: none; color: white;
                           font-size: 1.5rem; width: 40px; height: 40px; border-radius: 50%;
                           cursor: pointer; transition: var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body --}}
        <div style="padding: 24px; display: grid; gap: 24px;">

            {{-- 1. Informations générales --}}
            <div style="background: #f8fafc; padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--primary-color);">
                <h4 style="color: var(--primary-color); margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-info-circle"></i> Informations générales
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Nom <span style="color:var(--danger-color);">*</span>
                        </label>
                        <input x-model="formData.name" type="text" placeholder="ex. Siège Social Paris"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Code <span style="color:var(--danger-color);">*</span>
                        </label>
                        <input x-model="formData.code" type="text" placeholder="ex. PAR-HQ"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Description
                        </label>
                        <textarea x-model="formData.description" rows="3" placeholder="Description du site..."
                                  style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                         border-radius:var(--border-radius); font-family:var(--font-secondary);
                                         font-size:.95rem; resize:vertical; transition:var(--transition);"
                                  onfocus="this.style.borderColor='var(--primary-color)'"
                                  onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>

                </div>
            </div>

            {{-- 2. Localisation --}}
            <div style="background: #f0fdf4; padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--success-color);">
                <h4 style="color:var(--success-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-map-marker-alt"></i> Localisation
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">

                    <div style="grid-column: 1 / -1;">
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Adresse
                        </label>
                        <input x-model="formData.address" type="text" placeholder="ex. 123 Avenue des Champs-Élysées"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Code postal
                        </label>
                        <input x-model="formData.postal_code" type="text" placeholder="ex. 75008"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Ville
                        </label>
                        <input x-model="formData.city" type="text" placeholder="ex. Paris"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Région
                        </label>
                        <input x-model="formData.region" type="text" placeholder="ex. Île-de-France"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Pays
                        </label>
                        <input x-model="formData.country" type="text" placeholder="ex. France"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                </div>
            </div>

            {{-- 3. Coordonnées GPS (optionnel) --}}
            <div style="background: #e0f2fe; padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--info-color);">
                <h4 style="color:var(--info-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-globe"></i> Coordonnées GPS
                    <span style="font-weight:400; font-size:.85rem; color:var(--text-light);">(optionnel)</span>
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Latitude
                        </label>
                        <input x-model="formData.latitude" type="number" step="0.000001" placeholder="ex. 48.8566"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--info-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Longitude
                        </label>
                        <input x-model="formData.longitude" type="number" step="0.000001" placeholder="ex. 2.3522"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--info-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                </div>
            </div>

            {{-- 4. Informations de contact --}}
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                        padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--warning-color);">
                <h4 style="color:#92400e; margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-address-book"></i> Informations de contact
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-user"></i> Nom du contact
                        </label>
                        <input x-model="formData.contact_name" type="text" placeholder="ex. Jean Dupont"
                               style="width:100%; padding:10px 14px; border:2px solid #f59e0b;
                                      border-radius:var(--border-radius); background:white;
                                      font-family:var(--font-secondary); font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='#92400e'"
                               onblur="this.style.borderColor='#f59e0b'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input x-model="formData.contact_email" type="email" placeholder="ex. contact@site.fr"
                               style="width:100%; padding:10px 14px; border:2px solid #f59e0b;
                                      border-radius:var(--border-radius); background:white;
                                      font-family:monospace; font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='#92400e'"
                               onblur="this.style.borderColor='#f59e0b'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-phone"></i> Téléphone
                        </label>
                        <input x-model="formData.contact_phone" type="tel" placeholder="ex. +33 1 23 45 67 89"
                               style="width:100%; padding:10px 14px; border:2px solid #f59e0b;
                                      border-radius:var(--border-radius); background:white;
                                      font-family:monospace; font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='#92400e'"
                               onblur="this.style.borderColor='#f59e0b'">
                    </div>

                </div>
            </div>

            {{-- 5. Notes additionnelles --}}
            <div style="background: #f8fafc; padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--accent-color);">
                <h4 style="color:var(--accent-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-sticky-note"></i> Notes additionnelles
                    <span style="font-weight:400; font-size:.85rem; color:var(--text-light);">(optionnel)</span>
                </h4>
                <textarea x-model="formData.notes" rows="4"
                          placeholder="Informations complémentaires sur le site..."
                          style="width:100%; padding:12px 14px; border:2px solid var(--border-color);
                                 border-radius:var(--border-radius); font-family:var(--font-secondary);
                                 font-size:.88rem; line-height:1.6; resize:vertical;
                                 transition:var(--transition); color:var(--text-color);"
                          onfocus="this.style.borderColor='var(--accent-color)'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: flex-end; gap: 12px; background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('createEquipmentModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary" @click="saveEquipment()">
                <i class="fas fa-save"></i>
                <span x-text="modalData.id ? 'Enregistrer les modifications' : 'Créer le site'"></span>
            </button>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MODAL : Liste des équipements d'un site (nouveau)
     ════════════════════════════════════════════════════════════ --}}
<div id="viewSiteEquipmentModal"
     x-show="currentModal === 'siteEquipment'"
     x-cloak
     style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 800px; max-height: 90vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- Header --}}
        <div style="padding: 24px; border-bottom: 2px solid var(--border-color);
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas" :class="{
                    'fa-fire': modalSiteEquipmentType === 'firewall',
                    'fa-route': modalSiteEquipmentType === 'router',
                    'fa-exchange-alt': modalSiteEquipmentType === 'switch'
                }"></i>
                <span x-text="modalSiteEquipmentTitle"></span>
            </h3>
            <button @click="closeModal('viewSiteEquipmentModal')"
                    style="background: rgba(255,255,255,0.2); border: none; color: white;
                           font-size: 1.5rem; width: 40px; height: 40px; border-radius: 50%;
                           cursor: pointer; transition: var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body --}}
        <div style="padding: 24px;">
            <template x-if="modalSiteEquipmentList.length === 0">
                <p style="text-align: center; color: var(--text-light); padding: 40px;">
                    <i class="fas fa-info-circle fa-2x" style="margin-bottom: 12px; display: block;"></i>
                    Aucun équipement de ce type sur ce site.
                </p>
            </template>
            <template x-for="eq in modalSiteEquipmentList" :key="eq.id">
                <div style="display: flex; align-items: center; justify-content: space-between;
                            padding: 16px; border: 1px solid var(--border-color); border-radius: var(--border-radius);
                            margin-bottom: 12px; background: white; transition: var(--transition);"
                     @mouseenter="$el.style.borderColor = 'var(--primary-color)'; $el.style.boxShadow = 'var(--card-shadow)'"
                     @mouseleave="$el.style.borderColor = 'var(--border-color)'; $el.style.boxShadow = 'none'">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="font-size: 2rem; color: var(--primary-color);">
                            <i class="fas" :class="{
                                'fa-fire': modalSiteEquipmentType === 'firewall',
                                'fa-route': modalSiteEquipmentType === 'router',
                                'fa-exchange-alt': modalSiteEquipmentType === 'switch'
                            }"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700;" x-text="eq.name"></div>
                            <div style="font-size: 0.85rem; color: var(--text-light);">
                                <span x-text="eq.model || 'Modèle N/A'"></span>
                                <span x-show="eq.brand"> · <span x-text="eq.brand"></span></span>
                            </div>
                            <div style="font-size: 0.8rem; margin-top: 4px;">
                                <span class="status-badge" :class="{
                                    'status-active': eq.status === 'active',
                                    'status-warning': eq.status === 'warning',
                                    'status-danger': eq.status === 'danger'
                                }" x-text="eq.status === 'active' ? 'Actif' : (eq.status === 'warning' ? 'Avertissement' : 'Critique')"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline btn-sm btn-icon" title="Voir détails"
                                @click="viewItem(modalSiteEquipmentType + 's', eq.id); closeModal('viewSiteEquipmentModal')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: flex-end; gap: 12px;
                    background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('viewSiteEquipmentModal')">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>

    </div>
</div>

{{-- Ajout des propriétés et méthodes nécessaires dans l'objet Alpine --}}
