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

{{-- ══════════════════════════════════════════════════════════════
     MODAL DE CRÉATION / ÉDITION — FIREWALL
     ══════════════════════════════════════════════════════════════ --}}
<div id="createEquipmentModal"
     x-show="currentModal === 'create' && modalData.type === 'firewall'"
     x-cloak
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            display: flex; align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 860px; max-height: 92vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover);
                animation: fadeIn .3s ease;">

        {{-- HEADER --}}
        <div style="padding: 24px;
                    border-bottom: 2px solid var(--border-color);
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-fire"></i>
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

        {{-- BODY --}}
        <div style="padding: 24px; display: grid; gap: 24px;">

            {{-- ── 1. Informations générales ──────────────────────────── --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--primary-color);">
                <h4 style="color: var(--primary-color); margin: 0 0 16px;
                           display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-info-circle"></i> Informations générales
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Nom <span style="color:var(--danger-color);">*</span>
                        </label>
                        <input x-model="formData.name" type="text" placeholder="ex. FW-PARIS-01"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Site <span style="color:var(--danger-color);">*</span>
                        </label>
                        <select x-model="formData.site_id"
                                style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                       border-radius:var(--border-radius); font-family:var(--font-secondary);
                                       font-size:.95rem; background:white; transition:var(--transition);"
                                onfocus="this.style.borderColor='var(--primary-color)'"
                                onblur="this.style.borderColor='var(--border-color)'">
                            <option value="">— Sélectionner un site —</option>
                            <template x-for="site in sites" :key="site.id">
                                <option :value="site.id" x-text="site.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Marque
                        </label>
                        <input x-model="formData.brand" type="text" placeholder="ex. Fortinet, Palo Alto…"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Modèle
                        </label>
                        <input x-model="formData.model" type="text" placeholder="ex. FortiGate 100F"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:var(--font-secondary);
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Numéro de série
                        </label>
                        <input x-model="formData.serial_number" type="text" placeholder="ex. FG1H0E3919000000"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--primary-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Statut
                        </label>
                        <div style="display:flex; gap:12px; align-items:center; padding-top:6px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
                                <input type="radio" x-model="formData.status" :value="true"
                                       style="accent-color:var(--success-color); width:16px; height:16px;">
                                <span style="color:var(--success-color);"><i class="fas fa-check-circle"></i> Actif</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
                                <input type="radio" x-model="formData.status" :value="false"
                                       style="accent-color:var(--danger-color); width:16px; height:16px;">
                                <span style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> Inactif</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── 2. Credentials d'accès ─────────────────────────────── --}}
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                        padding: 20px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--warning-color);">
                <h4 style="color:#92400e; margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-key"></i> Credentials d'accès
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-user-shield"></i> Nom d'utilisateur
                        </label>
                        <input x-model="formData.username" type="text" placeholder="ex. admin"
                               style="width:100%; padding:10px 14px; border:2px solid #f59e0b;
                                      border-radius:var(--border-radius); background:white;
                                      font-family:monospace; font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='#92400e'"
                               onblur="this.style.borderColor='#f59e0b'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <div style="position:relative;">
                            <input x-model="formData.password"
                                   :type="formData._showPass ? 'text' : 'password'"
                                   placeholder="••••••••••••"
                                   style="width:100%; padding:10px 40px 10px 14px; border:2px solid #f59e0b;
                                          border-radius:var(--border-radius); background:white;
                                          font-family:monospace; font-size:.95rem; transition:var(--transition);"
                                   onfocus="this.style.borderColor='#92400e'"
                                   onblur="this.style.borderColor='#f59e0b'">
                            <button type="button" @click="formData._showPass = !formData._showPass"
                                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                           background:none; border:none; cursor:pointer; color:#92400e;">
                                <i class="fas" :class="formData._showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:#92400e; display:block; margin-bottom:6px; font-weight:600;">
                            <i class="fas fa-shield-alt"></i> Enable Password
                            <span style="font-weight:400; font-size:.8rem;">(optionnel)</span>
                        </label>
                        <div style="position:relative;">
                            <input x-model="formData.enable_password"
                                   :type="formData._showEnablePass ? 'text' : 'password'"
                                   placeholder="••••••••••••"
                                   style="width:100%; padding:10px 40px 10px 14px; border:2px solid #f59e0b;
                                          border-radius:var(--border-radius); background:white;
                                          font-family:monospace; font-size:.95rem; transition:var(--transition);"
                                   onfocus="this.style.borderColor='#92400e'"
                                   onblur="this.style.borderColor='#f59e0b'">
                            <button type="button" @click="formData._showEnablePass = !formData._showEnablePass"
                                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                           background:none; border:none; cursor:pointer; color:#92400e;">
                                <i class="fas" :class="formData._showEnablePass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── 3. Configuration réseau ────────────────────────────── --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--accent-color);">
                <h4 style="color:var(--accent-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-network-wired"></i> Configuration réseau
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">IP NMS</label>
                        <input x-model="formData.ip_nms" type="text" placeholder="ex. 10.0.1.1"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--accent-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">VLAN NMS</label>
                        <input x-model="formData.vlan_nms" type="number" placeholder="ex. 100"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--accent-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">IP Service</label>
                        <input x-model="formData.ip_service" type="text" placeholder="ex. 192.168.1.1"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--accent-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">VLAN Service</label>
                        <input x-model="formData.vlan_service" type="number" placeholder="ex. 200"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--accent-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                </div>
            </div>

            {{-- ── 4. Politiques de sécurité & Performance ───────────── --}}
            <div style="background: #fef2f2; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--danger-color);">
                <h4 style="color:var(--danger-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-shield-alt"></i> Politiques de sécurité &amp; Performance
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Nombre de règles
                        </label>
                        <input x-model="formData.security_policies_count" type="number" min="0" placeholder="ex. 150"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--danger-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            CPU (%)
                        </label>
                        <input x-model="formData.cpu" type="number" min="0" max="100" placeholder="ex. 42"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--danger-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Mémoire (%)
                        </label>
                        <input x-model="formData.memory" type="number" min="0" max="100" placeholder="ex. 67"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--danger-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                </div>
            </div>

            {{-- ── 5. Configuration (optionnel) ──────────────────────── --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--info-color);">
                <h4 style="color:var(--info-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-code"></i> Configuration
                    <span style="font-weight:400; font-size:.85rem; color:var(--text-light);">(optionnel)</span>
                </h4>
                <textarea x-model="formData.configuration"
                          rows="5"
                          placeholder="Collez ici la configuration initiale (FortiOS, PAN-OS, etc.)…"
                          style="width:100%; padding:12px 14px; border:2px solid var(--border-color);
                                 border-radius:var(--border-radius); font-family:monospace;
                                 font-size:.88rem; line-height:1.6; resize:vertical;
                                 transition:var(--transition); color:var(--text-color);"
                          onfocus="this.style.borderColor='var(--info-color)'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
            </div>

        </div>{{-- /body --}}

        {{-- FOOTER --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: flex-end; gap: 12px;
                    background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('createEquipmentModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary" @click="saveEquipment()">
                <i class="fas fa-save"></i>
                <span x-text="modalData.id ? 'Enregistrer les modifications' : 'Créer le firewall'"></span>
            </button>
        </div>

    </div>
</div>