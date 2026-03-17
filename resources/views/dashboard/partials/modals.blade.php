{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 1 : CRÉATION / ÉDITION
     Condition : currentModal === 'create'
     Type discriminant : modalData.type = 'switch' | 'router' | 'firewall' | 'site'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="createEquipmentModal"
     x-show="currentModal === 'create'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:860px;max-height:92vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid var(--border-color);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,var(--primary-color) 0%,var(--primary-dark) 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.5rem;display:flex;align-items:center;gap:12px;">
                <i class="fas"
                   :class="modalData.type==='site'     ? 'fa-building'
                          :modalData.type==='switch'   ? 'fa-exchange-alt'
                          :modalData.type==='router'   ? 'fa-route'
                          :modalData.type==='firewall' ? 'fa-fire'
                          :'fa-server'"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('createEquipmentModal')"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;
                           font-size:1.5rem;width:40px;height:40px;border-radius:50%;
                           cursor:pointer;transition:var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── BODY ──────────────────────────────────────────────────── --}}
        <div style="padding:24px;display:grid;gap:24px;">

            {{-- ══════════════════════════════════════════════════════════
                 FORMULAIRE SITE
                 ══════════════════════════════════════════════════════════ --}}
            <template x-if="modalData.type === 'site'">
                <div x-data="{
                    equipTab: 'switches',
                    selectedIds: { switches: [], routers: [], firewalls: [] },
                    lastAdded: null,
                    lastAddedType: null,

                    init() {
                        // En mode édition : pré-sélectionner les équipements déjà associés
                        if (modalData.id) {
                            const siteId = modalData.id;
                            this.selectedIds.switches  = switches.filter(e => e.site_id === siteId).map(e => e.id);
                            this.selectedIds.routers   = routers.filter(e => e.site_id === siteId).map(e => e.id);
                            this.selectedIds.firewalls = firewalls.filter(e => e.site_id === siteId).map(e => e.id);
                        }
                    },

                    toggle(type, id, name) {
                        const list = this.selectedIds[type];
                        const idx  = list.indexOf(id);
                        if (idx === -1) {
                            this.selectedIds[type] = [...list, id];
                            this.lastAdded = name;
                            this.lastAddedType = type;
                            setTimeout(() => { this.lastAdded = null; this.lastAddedType = null; }, 2500);
                        } else {
                            this.selectedIds[type] = list.filter((_, i) => i !== idx);
                        }
                        /* Synchroniser immédiatement vers formData du parent */
                        formData.switches_ids  = this.selectedIds.switches;
                        formData.routers_ids   = this.selectedIds.routers;
                        formData.firewalls_ids = this.selectedIds.firewalls;
                    },
                    isSelected(type, id) {
                        return this.selectedIds[type].includes(id);
                    },
                    totalSelected() {
                        return this.selectedIds.switches.length
                             + this.selectedIds.routers.length
                             + this.selectedIds.firewalls.length;
                    },

                    /* Équipements associés au site en cours d'édition */
                    associatedSwitches()  {
                        if (!modalData.id) return [];
                        return switches.filter(e => e.site_id === modalData.id);
                    },
                    associatedRouters()   {
                        if (!modalData.id) return [];
                        return routers.filter(e => e.site_id === modalData.id);
                    },
                    associatedFirewalls() {
                        if (!modalData.id) return [];
                        return firewalls.filter(e => e.site_id === modalData.id);
                    },
                    totalAssociated() {
                        return this.associatedSwitches().length
                             + this.associatedRouters().length
                             + this.associatedFirewalls().length;
                    }
                }"
                x-init="init()"
                style="display:grid;gap:24px;"
                {{-- Expose modalData.id pour x-data init() via attribut caché --}}
                :_modal-data-id="modalData.id || null">

                    {{-- ─── Référence interne pour init() ─────────────────── --}}
                    <span style="display:none;"
                          x-effect="
                            /* Synchroniser vers formData pour que saveEquipment() du parent puisse les lire */
                            formData.switches_ids  = selectedIds.switches;
                            formData.routers_ids   = selectedIds.routers;
                            formData.firewalls_ids = selectedIds.firewalls;
                          "></span>

                    {{-- 1. Informations générales --}}
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--primary-color);">
                        <h4 style="color:var(--primary-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-info-circle"></i> Informations générales
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Nom <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.name" type="text" placeholder="ex. Siège Social Paris"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Code <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.code" type="text" placeholder="ex. PAR-HQ"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div style="grid-column:1/-1;">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Description</label>
                                <textarea x-model="formData.description" rows="2" placeholder="Description du site..."
                                          style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);resize:vertical;"
                                          onfocus="this.style.borderColor='var(--primary-color)'"
                                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Localisation --}}
                    <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--success-color);">
                        <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-map-marker-alt"></i> Localisation
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div style="grid-column:1/-1;">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Adresse</label>
                                <input x-model="formData.address" type="text" placeholder="ex. 123 Avenue des Champs-Élysées"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Code postal</label>
                                <input x-model="formData.postal_code" type="text" placeholder="ex. 75008"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ville</label>
                                <input x-model="formData.city" type="text" placeholder="ex. Paris"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Pays</label>
                                <input x-model="formData.country" type="text" placeholder="ex. France"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                        </div>
                    </div>

                    {{-- 3. Contact --}}
                    <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);padding:20px;
                                border-radius:var(--border-radius);border-left:4px solid var(--warning-color);">
                        <h4 style="color:#92400e;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-address-book"></i> Informations de contact
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">Nom du contact</label>
                                <input x-model="formData.technical_contact" type="text" placeholder="ex. Jean Dupont"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;"
                                       onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">Email</label>
                                <input x-model="formData.technical_email" type="text" placeholder="ex. contact@site.fr"
                                       autocomplete="new-password"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                       onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">Téléphone</label>
                                <input x-model="formData.phone" type="text" placeholder="ex. +33 1 23 45 67 89"
                                       autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                       onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                            </div>
                        </div>
                    </div>

                    {{-- ══════════════════════════════════════════════════
                         4a. MODE ÉDITION — Équipements associés avec détails
                         ══════════════════════════════════════════════════ --}}
                    <template x-if="modalData.id">
                        <div style="display:grid;gap:16px;">

                            {{-- Bandeau récap équipements associés --}}
                            <div style="background:linear-gradient(135deg,#e0f2fe,#f0f9ff);
                                        padding:16px 20px;border-radius:var(--border-radius);
                                        border:2px solid var(--primary-color);
                                        display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <i class="fas fa-network-wired" style="font-size:1.4rem;color:var(--primary-color);"></i>
                                    <div>
                                        <div style="font-weight:700;color:var(--primary-color);font-size:1rem;">Équipements du site</div>
                                        <div style="font-size:.82rem;color:var(--text-light);" x-text="
                                            totalAssociated() + ' équipement(s) associé(s) · '
                                            + switches.filter(e => e.site_id === modalData.id).length + ' switch(es), '
                                            + routers.filter(e => e.site_id === modalData.id).length + ' routeur(s), '
                                            + firewalls.filter(e => e.site_id === modalData.id).length + ' firewall(s)'
                                        "></div>
                                    </div>
                                </div>
                                <span x-show="totalAssociated() === 0"
                                      style="background:#fef3c7;color:#92400e;padding:4px 12px;
                                             border-radius:16px;font-size:.8rem;font-weight:600;">
                                    <i class="fas fa-exclamation-triangle"></i> Aucun équipement associé
                                </span>
                            </div>

                            {{-- Onglets type avec compteurs réels --}}
                            <div x-data="{ detailTab: 'switches' }" style="display:grid;gap:12px;">

                                <div style="display:flex;gap:0;border-radius:var(--border-radius);overflow:hidden;
                                            border:2px solid var(--border-color);">
                                    <button type="button"
                                            @click="detailTab = 'switches'"
                                            :style="{
                                                flex:1, padding:'10px 8px', border:'none', cursor:'pointer',
                                                fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                background: detailTab === 'switches'
                                                    ? 'linear-gradient(135deg,var(--primary-color),#0284c7)'
                                                    : 'white',
                                                color: detailTab === 'switches' ? 'white' : 'var(--text-light)'
                                            }">
                                        <i class="fas fa-exchange-alt"></i> Switchs
                                        <span :style="{
                                                  background: detailTab==='switches' ? 'rgba(255,255,255,.25)' : '#e0f2fe',
                                                  color: detailTab==='switches' ? 'white' : 'var(--primary-color)',
                                                  borderRadius:'12px', padding:'1px 8px',
                                                  fontSize:'.75rem', marginLeft:'4px', fontWeight:700
                                              }"
                                              x-text="associatedSwitches().length"></span>
                                    </button>
                                    <button type="button"
                                            @click="detailTab = 'routers'"
                                            :style="{
                                                flex:1, padding:'10px 8px',
                                                border:'none', borderLeft:'2px solid var(--border-color)',
                                                cursor:'pointer', fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                background: detailTab === 'routers'
                                                    ? 'linear-gradient(135deg,var(--success-color),#059669)'
                                                    : 'white',
                                                color: detailTab === 'routers' ? 'white' : 'var(--text-light)'
                                            }">
                                        <i class="fas fa-route"></i> Routeurs
                                        <span :style="{
                                                  background: detailTab==='routers' ? 'rgba(255,255,255,.25)' : '#d1fae5',
                                                  color: detailTab==='routers' ? 'white' : 'var(--success-color)',
                                                  borderRadius:'12px', padding:'1px 8px',
                                                  fontSize:'.75rem', marginLeft:'4px', fontWeight:700
                                              }"
                                              x-text="associatedRouters().length"></span>
                                    </button>
                                    <button type="button"
                                            @click="detailTab = 'firewalls'"
                                            :style="{
                                                flex:1, padding:'10px 8px',
                                                border:'none', borderLeft:'2px solid var(--border-color)',
                                                cursor:'pointer', fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                background: detailTab === 'firewalls'
                                                    ? 'linear-gradient(135deg,var(--danger-color),#dc2626)'
                                                    : 'white',
                                                color: detailTab === 'firewalls' ? 'white' : 'var(--text-light)'
                                            }">
                                        <i class="fas fa-fire"></i> Firewalls
                                        <span :style="{
                                                  background: detailTab==='firewalls' ? 'rgba(255,255,255,.25)' : '#fee2e2',
                                                  color: detailTab==='firewalls' ? 'white' : 'var(--danger-color)',
                                                  borderRadius:'12px', padding:'1px 8px',
                                                  fontSize:'.75rem', marginLeft:'4px', fontWeight:700
                                              }"
                                              x-text="associatedFirewalls().length"></span>
                                    </button>
                                </div>

                                {{-- ── SWITCHS détaillés ── --}}
                                <div x-show="detailTab === 'switches'"
                                     style="display:grid;gap:10px;max-height:340px;overflow-y:auto;padding-right:2px;">

                                    <template x-if="associatedSwitches().length === 0">
                                        <div style="text-align:center;padding:32px;color:var(--text-light);
                                                    background:#f8fafc;border-radius:var(--border-radius);
                                                    border:2px dashed var(--border-color);">
                                            <i class="fas fa-exchange-alt fa-2x" style="display:block;margin-bottom:10px;opacity:.4;"></i>
                                            Aucun switch associé à ce site
                                        </div>
                                    </template>

                                    <template x-for="eq in associatedSwitches()" :key="'edit-sw-'+eq.id">
                                        <div style="background:white;border:2px solid #bae6fd;
                                                    border-radius:var(--border-radius);padding:14px 16px;
                                                    display:grid;grid-template-columns:auto 1fr auto;
                                                    gap:14px;align-items:start;">

                                            {{-- Icône + statut --}}
                                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding-top:2px;">
                                                <div style="width:40px;height:40px;border-radius:10px;
                                                            background:linear-gradient(135deg,var(--primary-color),#0284c7);
                                                            display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-exchange-alt" style="color:white;font-size:1.1rem;"></i>
                                                </div>
                                                <span class="status-badge"
                                                      :class="eq.status === 'active' ? 'status-active' : 'status-danger'"
                                                      style="font-size:.68rem;padding:2px 8px;"
                                                      x-text="eq.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>

                                            {{-- Détails --}}
                                            <div style="display:grid;gap:6px;">
                                                <div style="font-weight:700;font-size:.97rem;color:var(--text-color);" x-text="eq.name"></div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;color:var(--text-light);">
                                                    <span x-show="eq.brand || eq.model">
                                                        <i class="fas fa-microchip" style="width:12px;"></i>
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ')"></span>
                                                    </span>
                                                    <span x-show="eq.serial_number">
                                                        <i class="fas fa-barcode" style="width:12px;"></i>
                                                        <span x-text="eq.serial_number" style="font-family:monospace;"></span>
                                                    </span>
                                                </div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;">
                                                    <span x-show="eq.ip_nms"
                                                          style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        <i class="fas fa-network-wired" style="font-size:.7rem;"></i>
                                                        NMS: <span x-text="eq.ip_nms"></span>
                                                        <span x-show="eq.vlan_nms"> (VLAN <span x-text="eq.vlan_nms"></span>)</span>
                                                    </span>
                                                    <span x-show="eq.ip_service"
                                                          style="background:#f0fdf4;color:#15803d;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        <i class="fas fa-server" style="font-size:.7rem;"></i>
                                                        Svc: <span x-text="eq.ip_service"></span>
                                                    </span>
                                                </div>
                                                <div x-show="eq.ports_total" style="font-size:.8rem;color:var(--text-light);">
                                                    <i class="fas fa-plug" style="width:12px;color:var(--primary-color);"></i>
                                                    Ports : <strong x-text="eq.ports_used || 0"></strong>/<span x-text="eq.ports_total"></span> utilisés
                                                    <span x-show="eq.firmware_version"> · FW: <span x-text="eq.firmware_version" style="font-family:monospace;"></span></span>
                                                </div>
                                            </div>

                                            {{-- Action : Dissocier --}}
                                            <button type="button"
                                                    @click="toggle('switches', eq.id, eq.name)"
                                                    :title="isSelected('switches', eq.id) ? 'Dissocier ce switch' : 'Réassocier'"
                                                    :style="{
                                                        background: isSelected('switches', eq.id) ? '#fee2e2' : '#f0fdf4',
                                                        color: isSelected('switches', eq.id) ? 'var(--danger-color)' : 'var(--success-color)',
                                                        border: '2px solid',
                                                        borderColor: isSelected('switches', eq.id) ? '#fecaca' : '#bbf7d0',
                                                        borderRadius:'8px', padding:'6px 10px',
                                                        cursor:'pointer', fontSize:'.8rem', fontWeight:600,
                                                        display:'flex', alignItems:'center', gap:'4px',
                                                        transition:'all .2s', whiteSpace:'nowrap'
                                                    }">
                                                <i class="fas" :class="isSelected('switches', eq.id) ? 'fa-unlink' : 'fa-link'"></i>
                                                <span x-text="isSelected('switches', eq.id) ? 'Dissocier' : 'Associer'"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- ── ROUTEURS détaillés ── --}}
                                <div x-show="detailTab === 'routers'"
                                     style="display:grid;gap:10px;max-height:340px;overflow-y:auto;padding-right:2px;">

                                    <template x-if="associatedRouters().length === 0">
                                        <div style="text-align:center;padding:32px;color:var(--text-light);
                                                    background:#f8fafc;border-radius:var(--border-radius);
                                                    border:2px dashed var(--border-color);">
                                            <i class="fas fa-route fa-2x" style="display:block;margin-bottom:10px;opacity:.4;"></i>
                                            Aucun routeur associé à ce site
                                        </div>
                                    </template>

                                    <template x-for="eq in associatedRouters()" :key="'edit-rt-'+eq.id">
                                        <div style="background:white;border:2px solid #bbf7d0;
                                                    border-radius:var(--border-radius);padding:14px 16px;
                                                    display:grid;grid-template-columns:auto 1fr auto;
                                                    gap:14px;align-items:start;">
                                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding-top:2px;">
                                                <div style="width:40px;height:40px;border-radius:10px;
                                                            background:linear-gradient(135deg,var(--success-color),#059669);
                                                            display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-route" style="color:white;font-size:1.1rem;"></i>
                                                </div>
                                                <span class="status-badge"
                                                      :class="eq.status === 'active' ? 'status-active' : 'status-danger'"
                                                      style="font-size:.68rem;padding:2px 8px;"
                                                      x-text="eq.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>
                                            <div style="display:grid;gap:6px;">
                                                <div style="font-weight:700;font-size:.97rem;color:var(--text-color);" x-text="eq.name"></div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;color:var(--text-light);">
                                                    <span x-show="eq.brand || eq.model">
                                                        <i class="fas fa-microchip" style="width:12px;"></i>
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ')"></span>
                                                    </span>
                                                    <span x-show="eq.serial_number">
                                                        <i class="fas fa-barcode" style="width:12px;"></i>
                                                        <span x-text="eq.serial_number" style="font-family:monospace;"></span>
                                                    </span>
                                                </div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;">
                                                    <span x-show="eq.ip_nms"
                                                          style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        NMS: <span x-text="eq.ip_nms"></span>
                                                        <span x-show="eq.vlan_nms"> (VLAN <span x-text="eq.vlan_nms"></span>)</span>
                                                    </span>
                                                    <span x-show="eq.management_ip"
                                                          style="background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        Mgmt: <span x-text="eq.management_ip"></span>
                                                    </span>
                                                </div>
                                                <div x-show="eq.interfaces_count" style="font-size:.8rem;color:var(--text-light);">
                                                    <i class="fas fa-ethernet" style="width:12px;color:var(--success-color);"></i>
                                                    Interfaces : <strong x-text="eq.interfaces_up_count || 0"></strong>/<span x-text="eq.interfaces_count"></span> actives
                                                </div>
                                            </div>
                                            <button type="button"
                                                    @click="toggle('routers', eq.id, eq.name)"
                                                    :style="{
                                                        background: isSelected('routers', eq.id) ? '#fee2e2' : '#f0fdf4',
                                                        color: isSelected('routers', eq.id) ? 'var(--danger-color)' : 'var(--success-color)',
                                                        border: '2px solid',
                                                        borderColor: isSelected('routers', eq.id) ? '#fecaca' : '#bbf7d0',
                                                        borderRadius:'8px', padding:'6px 10px',
                                                        cursor:'pointer', fontSize:'.8rem', fontWeight:600,
                                                        display:'flex', alignItems:'center', gap:'4px',
                                                        transition:'all .2s', whiteSpace:'nowrap'
                                                    }">
                                                <i class="fas" :class="isSelected('routers', eq.id) ? 'fa-unlink' : 'fa-link'"></i>
                                                <span x-text="isSelected('routers', eq.id) ? 'Dissocier' : 'Associer'"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- ── FIREWALLS détaillés ── --}}
                                <div x-show="detailTab === 'firewalls'"
                                     style="display:grid;gap:10px;max-height:340px;overflow-y:auto;padding-right:2px;">

                                    <template x-if="associatedFirewalls().length === 0">
                                        <div style="text-align:center;padding:32px;color:var(--text-light);
                                                    background:#f8fafc;border-radius:var(--border-radius);
                                                    border:2px dashed var(--border-color);">
                                            <i class="fas fa-fire fa-2x" style="display:block;margin-bottom:10px;opacity:.4;"></i>
                                            Aucun firewall associé à ce site
                                        </div>
                                    </template>

                                    <template x-for="eq in associatedFirewalls()" :key="'edit-fw-'+eq.id">
                                        <div style="background:white;border:2px solid #fecaca;
                                                    border-radius:var(--border-radius);padding:14px 16px;
                                                    display:grid;grid-template-columns:auto 1fr auto;
                                                    gap:14px;align-items:start;">
                                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding-top:2px;">
                                                <div style="width:40px;height:40px;border-radius:10px;
                                                            background:linear-gradient(135deg,var(--danger-color),#dc2626);
                                                            display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-fire" style="color:white;font-size:1.1rem;"></i>
                                                </div>
                                                <span class="status-badge"
                                                      :class="eq.status === 'active' ? 'status-active' : 'status-danger'"
                                                      style="font-size:.68rem;padding:2px 8px;"
                                                      x-text="eq.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>
                                            <div style="display:grid;gap:6px;">
                                                <div style="font-weight:700;font-size:.97rem;color:var(--text-color);" x-text="eq.name"></div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;color:var(--text-light);">
                                                    <span x-show="eq.brand || eq.model">
                                                        <i class="fas fa-microchip" style="width:12px;"></i>
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ')"></span>
                                                    </span>
                                                    <span x-show="eq.serial_number">
                                                        <i class="fas fa-barcode" style="width:12px;"></i>
                                                        <span x-text="eq.serial_number" style="font-family:monospace;"></span>
                                                    </span>
                                                </div>
                                                <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:.8rem;">
                                                    <span x-show="eq.ip_nms"
                                                          style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        NMS: <span x-text="eq.ip_nms"></span>
                                                        <span x-show="eq.vlan_nms"> (VLAN <span x-text="eq.vlan_nms"></span>)</span>
                                                    </span>
                                                    <span x-show="eq.ip_service"
                                                          style="background:#f0fdf4;color:#15803d;padding:2px 8px;border-radius:8px;font-family:monospace;">
                                                        Svc: <span x-text="eq.ip_service"></span>
                                                    </span>
                                                </div>
                                                <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:.8rem;color:var(--text-light);">
                                                    <span x-show="eq.security_policies_count !== undefined">
                                                        <i class="fas fa-shield-alt" style="width:12px;color:var(--danger-color);"></i>
                                                        <span x-text="eq.security_policies_count || 0"></span> règles
                                                    </span>
                                                    <span x-show="eq.cpu !== undefined">
                                                        <i class="fas fa-tachometer-alt" style="width:12px;"></i>
                                                        CPU: <strong x-text="eq.cpu || 0"></strong>%
                                                        &nbsp;· RAM: <strong x-text="eq.memory || 0"></strong>%
                                                    </span>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    @click="toggle('firewalls', eq.id, eq.name)"
                                                    :style="{
                                                        background: isSelected('firewalls', eq.id) ? '#fee2e2' : '#f0fdf4',
                                                        color: isSelected('firewalls', eq.id) ? 'var(--danger-color)' : 'var(--success-color)',
                                                        border: '2px solid',
                                                        borderColor: isSelected('firewalls', eq.id) ? '#fecaca' : '#bbf7d0',
                                                        borderRadius:'8px', padding:'6px 10px',
                                                        cursor:'pointer', fontSize:'.8rem', fontWeight:600,
                                                        display:'flex', alignItems:'center', gap:'4px',
                                                        transition:'all .2s', whiteSpace:'nowrap'
                                                    }">
                                                <i class="fas" :class="isSelected('firewalls', eq.id) ? 'fa-unlink' : 'fa-link'"></i>
                                                <span x-text="isSelected('firewalls', eq.id) ? 'Dissocier' : 'Associer'"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                            </div>{{-- /x-data detailTab --}}

                            {{-- Séparateur "Ajouter d'autres équipements" --}}
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="flex:1;height:2px;background:var(--border-color);"></div>
                                <span style="font-size:.82rem;font-weight:600;color:var(--text-light);white-space:nowrap;">
                                    <i class="fas fa-plus-circle" style="color:var(--primary-color);"></i>
                                    Ajouter d'autres équipements
                                </span>
                                <div style="flex:1;height:2px;background:var(--border-color);"></div>
                            </div>

                            {{-- Sélection d'équipements non encore associés --}}
                            <div x-data="{ addTab: 'switches' }" style="background:#f0f9ff;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--primary-color);">

                                <div style="display:flex;gap:0;border-radius:var(--border-radius);overflow:hidden;
                                            border:2px solid var(--border-color);margin-bottom:14px;">
                                    <button type="button" @click="addTab='switches'"
                                            :style="{ flex:1, padding:'8px', border:'none', cursor:'pointer', fontWeight:600, fontSize:'.82rem', transition:'all .2s',
                                                      background: addTab==='switches' ? 'linear-gradient(135deg,var(--primary-color),#0284c7)' : 'white',
                                                      color: addTab==='switches' ? 'white' : 'var(--text-light)' }">
                                        <i class="fas fa-exchange-alt"></i> Switchs disponibles
                                        <span :style="{ background: addTab==='switches' ? 'rgba(255,255,255,.25)' : '#e0f2fe', color: addTab==='switches' ? 'white' : 'var(--primary-color)', borderRadius:'12px', padding:'1px 7px', fontSize:'.72rem', marginLeft:'4px' }"
                                              x-text="switches.filter(e => e.site_id !== modalData.id).length"></span>
                                    </button>
                                    <button type="button" @click="addTab='routers'"
                                            :style="{ flex:1, padding:'8px', border:'none', borderLeft:'2px solid var(--border-color)', cursor:'pointer', fontWeight:600, fontSize:'.82rem', transition:'all .2s',
                                                      background: addTab==='routers' ? 'linear-gradient(135deg,var(--success-color),#059669)' : 'white',
                                                      color: addTab==='routers' ? 'white' : 'var(--text-light)' }">
                                        <i class="fas fa-route"></i> Routeurs disponibles
                                        <span :style="{ background: addTab==='routers' ? 'rgba(255,255,255,.25)' : '#d1fae5', color: addTab==='routers' ? 'white' : 'var(--success-color)', borderRadius:'12px', padding:'1px 7px', fontSize:'.72rem', marginLeft:'4px' }"
                                              x-text="routers.filter(e => e.site_id !== modalData.id).length"></span>
                                    </button>
                                    <button type="button" @click="addTab='firewalls'"
                                            :style="{ flex:1, padding:'8px', border:'none', borderLeft:'2px solid var(--border-color)', cursor:'pointer', fontWeight:600, fontSize:'.82rem', transition:'all .2s',
                                                      background: addTab==='firewalls' ? 'linear-gradient(135deg,var(--danger-color),#dc2626)' : 'white',
                                                      color: addTab==='firewalls' ? 'white' : 'var(--text-light)' }">
                                        <i class="fas fa-fire"></i> Firewalls disponibles
                                        <span :style="{ background: addTab==='firewalls' ? 'rgba(255,255,255,.25)' : '#fee2e2', color: addTab==='firewalls' ? 'white' : 'var(--danger-color)', borderRadius:'12px', padding:'1px 7px', fontSize:'.72rem', marginLeft:'4px' }"
                                              x-text="firewalls.filter(e => e.site_id !== modalData.id).length"></span>
                                    </button>
                                </div>

                                {{-- Toast confirmation --}}
                                <div x-show="lastAdded !== null"
                                     style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);
                                            border:2px solid var(--success-color);border-radius:var(--border-radius);
                                            padding:8px 14px;margin-bottom:12px;
                                            display:flex;align-items:center;gap:8px;font-weight:600;color:#065f46;font-size:.88rem;">
                                    <i class="fas fa-check-circle" style="color:var(--success-color);"></i>
                                    <span><strong x-text="lastAdded"></strong> ajouté au site ✓</span>
                                </div>

                                {{-- Liste switches disponibles --}}
                                <div x-show="addTab === 'switches'"
                                     style="max-height:200px;overflow-y:auto;display:grid;gap:8px;">
                                    <template x-if="switches.filter(e => e.site_id !== modalData.id).length === 0">
                                        <div style="text-align:center;padding:20px;color:var(--text-light);font-size:.88rem;">
                                            <i class="fas fa-check-circle" style="color:var(--success-color);margin-right:6px;"></i>
                                            Tous les switches sont déjà associés
                                        </div>
                                    </template>
                                    <template x-for="eq in switches.filter(e => e.site_id !== modalData.id)" :key="'add-sw-'+eq.id">
                                        <div @click="toggle('switches', eq.id, eq.name)"
                                             :style="{
                                                 background: isSelected('switches', eq.id) ? 'linear-gradient(135deg,#e0f2fe,#bae6fd)' : 'white',
                                                 borderColor: isSelected('switches', eq.id) ? 'var(--primary-color)' : 'var(--border-color)',
                                                 padding:'10px 14px', borderRadius:'var(--border-radius)',
                                                 border:'2px solid', display:'flex', alignItems:'center',
                                                 justifyContent:'space-between', cursor:'pointer', transition:'all .2s'
                                             }">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <div :style="{
                                                         width:'20px', height:'20px', borderRadius:'5px', border:'2px solid', flexShrink:0,
                                                         display:'flex', alignItems:'center', justifyContent:'center',
                                                         background: isSelected('switches', eq.id) ? 'var(--primary-color)' : 'white',
                                                         borderColor: isSelected('switches', eq.id) ? 'var(--primary-color)' : 'var(--border-color)'
                                                     }">
                                                    <i class="fas fa-check" x-show="isSelected('switches', eq.id)" style="color:white;font-size:.6rem;"></i>
                                                </div>
                                                <i class="fas fa-exchange-alt" :style="{ color: isSelected('switches', eq.id) ? 'var(--primary-color)' : 'var(--text-light)' }"></i>
                                                <div>
                                                    <div style="font-weight:700;font-size:.9rem;" x-text="eq.name"></div>
                                                    <div style="font-size:.75rem;color:var(--text-light);">
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ') || 'N/A'"></span>
                                                        <span x-show="eq.site"> · Actuellement sur : <strong x-text="eq.site"></strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'"
                                                  style="font-size:.68rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Liste routeurs disponibles --}}
                                <div x-show="addTab === 'routers'"
                                     style="max-height:200px;overflow-y:auto;display:grid;gap:8px;">
                                    <template x-if="routers.filter(e => e.site_id !== modalData.id).length === 0">
                                        <div style="text-align:center;padding:20px;color:var(--text-light);font-size:.88rem;">
                                            <i class="fas fa-check-circle" style="color:var(--success-color);margin-right:6px;"></i>
                                            Tous les routeurs sont déjà associés
                                        </div>
                                    </template>
                                    <template x-for="eq in routers.filter(e => e.site_id !== modalData.id)" :key="'add-rt-'+eq.id">
                                        <div @click="toggle('routers', eq.id, eq.name)"
                                             :style="{
                                                 background: isSelected('routers', eq.id) ? 'linear-gradient(135deg,#d1fae5,#a7f3d0)' : 'white',
                                                 borderColor: isSelected('routers', eq.id) ? 'var(--success-color)' : 'var(--border-color)',
                                                 padding:'10px 14px', borderRadius:'var(--border-radius)',
                                                 border:'2px solid', display:'flex', alignItems:'center',
                                                 justifyContent:'space-between', cursor:'pointer', transition:'all .2s'
                                             }">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <div :style="{
                                                         width:'20px', height:'20px', borderRadius:'5px', border:'2px solid', flexShrink:0,
                                                         display:'flex', alignItems:'center', justifyContent:'center',
                                                         background: isSelected('routers', eq.id) ? 'var(--success-color)' : 'white',
                                                         borderColor: isSelected('routers', eq.id) ? 'var(--success-color)' : 'var(--border-color)'
                                                     }">
                                                    <i class="fas fa-check" x-show="isSelected('routers', eq.id)" style="color:white;font-size:.6rem;"></i>
                                                </div>
                                                <i class="fas fa-route" :style="{ color: isSelected('routers', eq.id) ? 'var(--success-color)' : 'var(--text-light)' }"></i>
                                                <div>
                                                    <div style="font-weight:700;font-size:.9rem;" x-text="eq.name"></div>
                                                    <div style="font-size:.75rem;color:var(--text-light);">
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ') || 'N/A'"></span>
                                                        <span x-show="eq.site"> · Actuellement sur : <strong x-text="eq.site"></strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'"
                                                  style="font-size:.68rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Liste firewalls disponibles --}}
                                <div x-show="addTab === 'firewalls'"
                                     style="max-height:200px;overflow-y:auto;display:grid;gap:8px;">
                                    <template x-if="firewalls.filter(e => e.site_id !== modalData.id).length === 0">
                                        <div style="text-align:center;padding:20px;color:var(--text-light);font-size:.88rem;">
                                            <i class="fas fa-check-circle" style="color:var(--success-color);margin-right:6px;"></i>
                                            Tous les firewalls sont déjà associés
                                        </div>
                                    </template>
                                    <template x-for="eq in firewalls.filter(e => e.site_id !== modalData.id)" :key="'add-fw-'+eq.id">
                                        <div @click="toggle('firewalls', eq.id, eq.name)"
                                             :style="{
                                                 background: isSelected('firewalls', eq.id) ? 'linear-gradient(135deg,#fee2e2,#fecaca)' : 'white',
                                                 borderColor: isSelected('firewalls', eq.id) ? 'var(--danger-color)' : 'var(--border-color)',
                                                 padding:'10px 14px', borderRadius:'var(--border-radius)',
                                                 border:'2px solid', display:'flex', alignItems:'center',
                                                 justifyContent:'space-between', cursor:'pointer', transition:'all .2s'
                                             }">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <div :style="{
                                                         width:'20px', height:'20px', borderRadius:'5px', border:'2px solid', flexShrink:0,
                                                         display:'flex', alignItems:'center', justifyContent:'center',
                                                         background: isSelected('firewalls', eq.id) ? 'var(--danger-color)' : 'white',
                                                         borderColor: isSelected('firewalls', eq.id) ? 'var(--danger-color)' : 'var(--border-color)'
                                                     }">
                                                    <i class="fas fa-check" x-show="isSelected('firewalls', eq.id)" style="color:white;font-size:.6rem;"></i>
                                                </div>
                                                <i class="fas fa-fire" :style="{ color: isSelected('firewalls', eq.id) ? 'var(--danger-color)' : 'var(--text-light)' }"></i>
                                                <div>
                                                    <div style="font-weight:700;font-size:.9rem;" x-text="eq.name"></div>
                                                    <div style="font-size:.75rem;color:var(--text-light);">
                                                        <span x-text="[eq.brand, eq.model].filter(Boolean).join(' ') || 'N/A'"></span>
                                                        <span x-show="eq.site"> · Actuellement sur : <strong x-text="eq.site"></strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'"
                                                  style="font-size:.68rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                        </div>
                                    </template>
                                </div>

                            </div>{{-- /ajout équipements --}}

                        </div>
                    </template>
                    {{-- /mode édition --}}

                    {{-- ══════════════════════════════════════════════════
                         4b. MODE CRÉATION — sélection multi-type (inchangé)
                         ══════════════════════════════════════════════════ --}}
                    <template x-if="!modalData.id">
                        <div style="background:#f0f9ff;padding:20px;border-radius:var(--border-radius);
                                    border-left:4px solid var(--primary-color);">

                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                                <h4 style="color:var(--primary-color);margin:0;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-network-wired"></i> Associer des équipements
                                </h4>
                                <span x-show="totalSelected() > 0"
                                      style="background:linear-gradient(135deg,var(--primary-color),var(--accent-color));
                                             color:white;padding:4px 14px;border-radius:20px;font-size:.8rem;font-weight:700;
                                             display:flex;align-items:center;gap:6px;">
                                    <i class="fas fa-check-circle"></i>
                                    <span x-text="totalSelected()"></span> sélectionné(s)
                                </span>
                            </div>

                            <div x-show="lastAdded !== null"
                                 style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);
                                        border:2px solid var(--success-color);border-radius:var(--border-radius);
                                        padding:10px 16px;margin-bottom:14px;
                                        display:flex;align-items:center;gap:10px;font-weight:600;color:#065f46;">
                                <i class="fas fa-check-circle" style="font-size:1.2rem;color:var(--success-color);"></i>
                                <span><strong x-text="lastAdded"></strong> a été associé au site ✓</span>
                            </div>

                            {{-- Onglets --}}
                            <div style="display:flex;gap:0;border-radius:var(--border-radius);overflow:hidden;
                                        border:2px solid var(--border-color);margin-bottom:14px;">
                                <button type="button" @click="equipTab = 'switches'"
                                        :style="{ flex:1, padding:'10px 8px', border:'none', cursor:'pointer', fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                  background: equipTab==='switches' ? 'linear-gradient(135deg,var(--primary-color),#0284c7)' : 'white',
                                                  color: equipTab==='switches' ? 'white' : 'var(--text-light)' }">
                                    <i class="fas fa-exchange-alt"></i> Switchs
                                    <span x-show="selectedIds.switches.length > 0"
                                          style="background:rgba(255,255,255,.3);border-radius:12px;padding:1px 7px;font-size:.75rem;margin-left:4px;"
                                          x-text="selectedIds.switches.length"></span>
                                </button>
                                <button type="button" @click="equipTab = 'routers'"
                                        :style="{ flex:1, padding:'10px 8px', border:'none', borderLeft:'2px solid var(--border-color)', cursor:'pointer', fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                  background: equipTab==='routers' ? 'linear-gradient(135deg,var(--success-color),#059669)' : 'white',
                                                  color: equipTab==='routers' ? 'white' : 'var(--text-light)' }">
                                    <i class="fas fa-route"></i> Routeurs
                                    <span x-show="selectedIds.routers.length > 0"
                                          style="background:rgba(255,255,255,.3);border-radius:12px;padding:1px 7px;font-size:.75rem;margin-left:4px;"
                                          x-text="selectedIds.routers.length"></span>
                                </button>
                                <button type="button" @click="equipTab = 'firewalls'"
                                        :style="{ flex:1, padding:'10px 8px', border:'none', borderLeft:'2px solid var(--border-color)', cursor:'pointer', fontWeight:600, fontSize:'.85rem', transition:'all .2s',
                                                  background: equipTab==='firewalls' ? 'linear-gradient(135deg,var(--danger-color),#dc2626)' : 'white',
                                                  color: equipTab==='firewalls' ? 'white' : 'var(--text-light)' }">
                                    <i class="fas fa-fire"></i> Firewalls
                                    <span x-show="selectedIds.firewalls.length > 0"
                                          style="background:rgba(255,255,255,.3);border-radius:12px;padding:1px 7px;font-size:.75rem;margin-left:4px;"
                                          x-text="selectedIds.firewalls.length"></span>
                                </button>
                            </div>

                            {{-- Switchs --}}
                            <div x-show="equipTab === 'switches'" style="max-height:260px;overflow-y:auto;display:grid;gap:8px;">
                                <template x-if="switches.length === 0">
                                    <div style="text-align:center;padding:30px;color:var(--text-light);">
                                        <i class="fas fa-inbox fa-2x" style="display:block;margin-bottom:8px;"></i>Aucun switch disponible
                                    </div>
                                </template>
                                <template x-for="eq in switches" :key="eq.id">
                                    <div @click="toggle('switches', eq.id, eq.name)"
                                         :style="{ background: isSelected('switches',eq.id)?'linear-gradient(135deg,#e0f2fe,#bae6fd)':'white', borderColor: isSelected('switches',eq.id)?'var(--primary-color)':'var(--border-color)', padding:'10px 14px', borderRadius:'var(--border-radius)', border:'2px solid', display:'flex', alignItems:'center', justifyContent:'space-between', cursor:'pointer', transition:'all .2s' }">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div :style="{ width:'22px',height:'22px',borderRadius:'6px',border:'2px solid',flexShrink:0,display:'flex',alignItems:'center',justifyContent:'center',background:isSelected('switches',eq.id)?'var(--primary-color)':'white',borderColor:isSelected('switches',eq.id)?'var(--primary-color)':'var(--border-color)' }">
                                                <i class="fas fa-check" x-show="isSelected('switches',eq.id)" style="color:white;font-size:.7rem;"></i>
                                            </div>
                                            <i class="fas fa-exchange-alt" :style="{color:isSelected('switches',eq.id)?'var(--primary-color)':'var(--text-light)'}"></i>
                                            <div>
                                                <div style="font-weight:700;font-size:.92rem;" x-text="eq.name"></div>
                                                <div style="font-size:.76rem;color:var(--text-light);"><span x-text="eq.model||'N/A'"></span><span x-show="eq.site"> · <span x-text="eq.site"></span></span></div>
                                            </div>
                                        </div>
                                        <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'" style="font-size:.7rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Routeurs --}}
                            <div x-show="equipTab === 'routers'" style="max-height:260px;overflow-y:auto;display:grid;gap:8px;">
                                <template x-if="routers.length === 0">
                                    <div style="text-align:center;padding:30px;color:var(--text-light);">
                                        <i class="fas fa-inbox fa-2x" style="display:block;margin-bottom:8px;"></i>Aucun routeur disponible
                                    </div>
                                </template>
                                <template x-for="eq in routers" :key="eq.id">
                                    <div @click="toggle('routers', eq.id, eq.name)"
                                         :style="{ background: isSelected('routers',eq.id)?'linear-gradient(135deg,#d1fae5,#a7f3d0)':'white', borderColor: isSelected('routers',eq.id)?'var(--success-color)':'var(--border-color)', padding:'10px 14px', borderRadius:'var(--border-radius)', border:'2px solid', display:'flex', alignItems:'center', justifyContent:'space-between', cursor:'pointer', transition:'all .2s' }">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div :style="{ width:'22px',height:'22px',borderRadius:'6px',border:'2px solid',flexShrink:0,display:'flex',alignItems:'center',justifyContent:'center',background:isSelected('routers',eq.id)?'var(--success-color)':'white',borderColor:isSelected('routers',eq.id)?'var(--success-color)':'var(--border-color)' }">
                                                <i class="fas fa-check" x-show="isSelected('routers',eq.id)" style="color:white;font-size:.7rem;"></i>
                                            </div>
                                            <i class="fas fa-route" :style="{color:isSelected('routers',eq.id)?'var(--success-color)':'var(--text-light)'}"></i>
                                            <div>
                                                <div style="font-weight:700;font-size:.92rem;" x-text="eq.name"></div>
                                                <div style="font-size:.76rem;color:var(--text-light);"><span x-text="eq.model||'N/A'"></span><span x-show="eq.site"> · <span x-text="eq.site"></span></span></div>
                                            </div>
                                        </div>
                                        <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'" style="font-size:.7rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Firewalls --}}
                            <div x-show="equipTab === 'firewalls'" style="max-height:260px;overflow-y:auto;display:grid;gap:8px;">
                                <template x-if="firewalls.length === 0">
                                    <div style="text-align:center;padding:30px;color:var(--text-light);">
                                        <i class="fas fa-inbox fa-2x" style="display:block;margin-bottom:8px;"></i>Aucun firewall disponible
                                    </div>
                                </template>
                                <template x-for="eq in firewalls" :key="eq.id">
                                    <div @click="toggle('firewalls', eq.id, eq.name)"
                                         :style="{ background: isSelected('firewalls',eq.id)?'linear-gradient(135deg,#fee2e2,#fecaca)':'white', borderColor: isSelected('firewalls',eq.id)?'var(--danger-color)':'var(--border-color)', padding:'10px 14px', borderRadius:'var(--border-radius)', border:'2px solid', display:'flex', alignItems:'center', justifyContent:'space-between', cursor:'pointer', transition:'all .2s' }">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div :style="{ width:'22px',height:'22px',borderRadius:'6px',border:'2px solid',flexShrink:0,display:'flex',alignItems:'center',justifyContent:'center',background:isSelected('firewalls',eq.id)?'var(--danger-color)':'white',borderColor:isSelected('firewalls',eq.id)?'var(--danger-color)':'var(--border-color)' }">
                                                <i class="fas fa-check" x-show="isSelected('firewalls',eq.id)" style="color:white;font-size:.7rem;"></i>
                                            </div>
                                            <i class="fas fa-fire" :style="{color:isSelected('firewalls',eq.id)?'var(--danger-color)':'var(--text-light)'}"></i>
                                            <div>
                                                <div style="font-weight:700;font-size:.92rem;" x-text="eq.name"></div>
                                                <div style="font-size:.76rem;color:var(--text-light);"><span x-text="eq.model||'N/A'"></span><span x-show="eq.site"> · <span x-text="eq.site"></span></span></div>
                                            </div>
                                        </div>
                                        <span class="status-badge" :class="eq.status==='active'?'status-active':'status-danger'" style="font-size:.7rem;" x-text="eq.status==='active'?'Actif':'Inactif'"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Récapitulatif --}}
                            <template x-if="totalSelected() > 0">
                                <div style="margin-top:14px;padding:12px 16px;background:white;
                                            border-radius:var(--border-radius);border:2px dashed var(--primary-color);">
                                    <div style="font-size:.8rem;font-weight:700;color:var(--primary-color);margin-bottom:8px;">
                                        <i class="fas fa-clipboard-list"></i> Récapitulatif des associations
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                        <template x-for="id in selectedIds.switches" :key="'sw-'+id">
                                            <span style="background:#e0f2fe;color:var(--primary-color);padding:3px 10px;border-radius:12px;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                                                <i class="fas fa-exchange-alt"></i>
                                                <span x-text="switches.find(s=>s.id===id)?.name||id"></span>
                                                <button type="button" @click.stop="selectedIds.switches.splice(selectedIds.switches.indexOf(id),1)"
                                                        style="background:none;border:none;cursor:pointer;color:inherit;padding:0;margin-left:2px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                        <template x-for="id in selectedIds.routers" :key="'rt-'+id">
                                            <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:12px;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                                                <i class="fas fa-route"></i>
                                                <span x-text="routers.find(r=>r.id===id)?.name||id"></span>
                                                <button type="button" @click.stop="selectedIds.routers.splice(selectedIds.routers.indexOf(id),1)"
                                                        style="background:none;border:none;cursor:pointer;color:inherit;padding:0;margin-left:2px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                        <template x-for="id in selectedIds.firewalls" :key="'fw-'+id">
                                            <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:12px;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:5px;">
                                                <i class="fas fa-fire"></i>
                                                <span x-text="firewalls.find(f=>f.id===id)?.name||id"></span>
                                                <button type="button" @click.stop="selectedIds.firewalls.splice(selectedIds.firewalls.indexOf(id),1)"
                                                        style="background:none;border:none;cursor:pointer;color:inherit;padding:0;margin-left:2px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                    {{-- /mode création --}}

                </div>{{-- /x-data site --}}
            </template>
            {{-- ── FIN FORMULAIRE SITE ──────────────────────────────── --}}
            <template x-if="modalData.type === 'user'">
                <div style="display:grid;gap:24px;">

                    {{-- 1. Informations personnelles --}}
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--primary-color);">
                        <h4 style="color:var(--primary-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-user-circle"></i> Informations personnelles
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nom complet <span style="color:var(--danger-color);">*</span></label>
                                <input x-model="formData.name" type="text" placeholder="ex. Jean Dupont" autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;"
                                       onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Adresse email <span style="color:var(--danger-color);">*</span></label>
                                <input x-model="formData.email" type="email" placeholder="ex. jean.dupont@entreprise.fr" autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Département</label>
                                <input x-model="formData.department" type="text" placeholder="ex. IT, Network, Security" autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;"
                                       onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Téléphone</label>
                                <input x-model="formData.phone" type="text" placeholder="ex. +33 6 12 34 56 78" autocomplete="off"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Rôle & Statut --}}
                    <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--warning-color);">
                        <h4 style="color:#92400e;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-shield-alt"></i> Rôle & Accès
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">Rôle <span style="color:var(--danger-color);">*</span></label>
                                <select x-model="formData.role"
                                        style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-size:.95rem;cursor:pointer;"
                                        onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                                    <option value="admin">👑 Administrateur</option>
                                    <option value="agent">🔧 Agent</option>
                                    <option value="viewer">👁 Lecteur</option>
                                </select>
                                <div style="margin-top:8px;padding:8px 12px;border-radius:8px;font-size:.8rem;font-weight:500;"
                                     :style="{ background: formData.role==='admin' ? '#fee2e2' : formData.role==='agent' ? '#fef3c7' : '#e0e7ff', color: formData.role==='admin' ? '#991b1b' : formData.role==='agent' ? '#92400e' : '#3730a3' }">
                                    <i class="fas" :class="formData.role==='admin' ? 'fa-crown' : formData.role==='agent' ? 'fa-user-cog' : 'fa-eye'"></i>
                                    <span x-text="formData.role==='admin' ? 'Accès complet : création, modification, suppression de tous les équipements et utilisateurs.' : formData.role==='agent' ? 'Peut créer et modifier les équipements. Ne peut pas gérer les utilisateurs.' : 'Lecture seule. Aucune modification possible.'"></span>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">Statut du compte</label>
                                <div style="display:flex;gap:16px;align-items:center;padding-top:6px;">
                                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;padding:10px 16px;border-radius:var(--border-radius);border:2px solid;transition:all .2s;"
                                           :style="{ borderColor:formData.is_active?'var(--success-color)':'var(--border-color)', background:formData.is_active?'#d1fae5':'white', color:formData.is_active?'#065f46':'var(--text-light)' }">
                                        <input type="radio" x-model="formData.is_active" :value="true" style="accent-color:var(--success-color);width:16px;height:16px;">
                                        <span><i class="fas fa-check-circle"></i> Actif</span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;padding:10px 16px;border-radius:var(--border-radius);border:2px solid;transition:all .2s;"
                                           :style="{ borderColor:!formData.is_active?'var(--danger-color)':'var(--border-color)', background:!formData.is_active?'#fee2e2':'white', color:!formData.is_active?'#991b1b':'var(--text-light)' }">
                                        <input type="radio" x-model="formData.is_active" :value="false" style="accent-color:var(--danger-color);width:16px;height:16px;">
                                        <span><i class="fas fa-times-circle"></i> Inactif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Mot de passe --}}
                    <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--success-color);">
                        <h4 style="color:var(--success-color);margin:0 0 6px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-lock"></i> Mot de passe
                            <span x-show="modalData.id" style="font-size:.8rem;font-weight:400;color:var(--text-light);margin-left:4px;">(laisser vide pour conserver l'actuel)</span>
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:12px;">
                            <div x-data="{ show: false }">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Mot de passe <span x-show="!modalData.id" style="color:var(--danger-color);">*</span></label>
                                <div style="position:relative;">
                                    <input x-model="formData.password" :type="show?'text':'password'" placeholder="••••••••••••" autocomplete="new-password"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                                           onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'">
                                    <button type="button" @click="show=!show" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-light);">
                                        <i class="fas" :class="show?'fa-eye-slash':'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div x-data="{ show: false }">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Confirmation <span x-show="!modalData.id" style="color:var(--danger-color);">*</span></label>
                                <div style="position:relative;">
                                    <input x-model="formData.password_confirmation" :type="show?'text':'password'" placeholder="••••••••••••" autocomplete="new-password"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                                           onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'">
                                    <button type="button" @click="show=!show" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-light);">
                                        <i class="fas" :class="show?'fa-eye-slash':'fa-eye'"></i>
                                    </button>
                                </div>
                                <div x-show="formData.password_confirmation"
                                     style="margin-top:6px;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:4px;"
                                     :style="{ color: formData.password===formData.password_confirmation ? 'var(--success-color)' : 'var(--danger-color)' }">
                                    <i class="fas" :class="formData.password===formData.password_confirmation ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="formData.password===formData.password_confirmation ? 'Les mots de passe correspondent' : 'Les mots de passe ne correspondent pas'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </template>
            {{-- ══════════════════════════════════════════════════════════
                 FORMULAIRE ÉQUIPEMENT (switch / router / firewall)
                 ══════════════════════════════════════════════════════════ --}}
            <template x-if="modalData.type !== 'site' && modalData.type !== 'user'">
                <div style="display:contents;">

            {{-- ① Informations générales --}}
            <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--primary-color);">
                <h4 style="color:var(--primary-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-info-circle"></i> Informations générales
                </h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nom <span style="color:var(--danger-color);">*</span></label>
                        <input x-model="formData.name" type="text"
                               :placeholder="modalData.type==='switch'?'ex. SW-PARIS-01':modalData.type==='router'?'ex. RT-PARIS-01':'ex. FW-PARIS-01'"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Site <span style="color:var(--danger-color);">*</span></label>
                        <select x-model="formData.site_id"
                                style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);background:white;font-size:.95rem;"
                                onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                            <option value="">— Sélectionner un site —</option>
                            <template x-for="site in sites" :key="site.id">
                                <option :value="site.id" x-text="site.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Marque</label>
                        <input x-model="formData.brand" type="text"
                               :placeholder="modalData.type==='firewall'?'ex. Fortinet…':modalData.type==='router'?'ex. Cisco…':'ex. Cisco, HP…'"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Modèle</label>
                        <input x-model="formData.model" type="text"
                               :placeholder="modalData.type==='firewall'?'ex. FortiGate 100F':modalData.type==='router'?'ex. Cisco ISR 4451':'ex. Catalyst 9300'"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Numéro de série</label>
                        <input x-model="formData.serial_number" type="text" placeholder="ex. FDO2049Z0CL"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Statut</label>
                        <div style="display:flex;gap:12px;align-items:center;padding-top:6px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                <input type="radio" x-model="formData.status" value="active" style="accent-color:var(--success-color);width:16px;height:16px;">
                                <span style="color:var(--success-color);"><i class="fas fa-check-circle"></i> Actif</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                <input type="radio" x-model="formData.status" value="danger" style="accent-color:var(--danger-color);width:16px;height:16px;">
                                <span style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> Inactif</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ② Credentials --}}
            <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--warning-color);">
                <h4 style="color:#92400e;margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-key"></i> Credentials d'accès</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                    <div>
                        <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;"><i class="fas fa-user-shield"></i> Nom d'utilisateur</label>
                        <input x-model="formData.username" type="text" placeholder="ex. admin"
                               style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;"><i class="fas fa-lock"></i> Mot de passe</label>
                        <div style="position:relative;">
                            <input x-model="formData.password" :type="formData._showPass?'text':'password'" placeholder="••••••••••••"
                                   style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;font-size:.95rem;"
                                   onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                            <button type="button" @click="formData._showPass=!formData._showPass"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#92400e;">
                                <i class="fas" :class="formData._showPass?'fa-eye-slash':'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;"><i class="fas fa-shield-alt"></i> Enable Password <span style="font-weight:400;font-size:.8rem;">(optionnel)</span></label>
                        <div style="position:relative;">
                            <input x-model="formData.enable_password" :type="formData._showEnablePass?'text':'password'" placeholder="••••••••••••"
                                   style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;font-size:.95rem;"
                                   onfocus="this.style.borderColor='#92400e'" onblur="this.style.borderColor='#f59e0b'">
                            <button type="button" @click="formData._showEnablePass=!formData._showEnablePass"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#92400e;">
                                <i class="fas" :class="formData._showEnablePass?'fa-eye-slash':'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ③ Configuration réseau --}}
            <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--accent-color);">
                <h4 style="color:var(--accent-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-network-wired"></i> Configuration réseau</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP NMS</label>
                        <input x-model="formData.ip_nms" type="text" placeholder="ex. 10.0.1.10"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">VLAN NMS</label>
                        <input x-model="formData.vlan_nms" type="number" placeholder="ex. 100"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP Service</label>
                        <input x-model="formData.ip_service" type="text" placeholder="ex. 192.168.10.1"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <div>
                        <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">VLAN Service</label>
                        <input x-model="formData.vlan_service" type="number" placeholder="ex. 200"
                               style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                               onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'">
                    </div>
                    <template x-if="modalData.type === 'router'">
                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP Management</label>
                            <input x-model="formData.management_ip" type="text" placeholder="ex. 172.16.0.1"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.95rem;"
                                   onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'">
                        </div>
                    </template>
                </div>
            </div>

            {{-- ④ Spécifique switch --}}
            <template x-if="modalData.type === 'switch'">
                <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--success-color);">
                    <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-plug"></i> Ports &amp; VLANs</h4>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ports total</label><input x-model="formData.ports_total" type="number" min="1" placeholder="ex. 48" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ports utilisés</label><input x-model="formData.ports_used" type="number" min="0" placeholder="ex. 32" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre de VLANs</label><input x-model="formData.vlans" type="number" min="1" placeholder="ex. 10" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Version firmware</label><input x-model="formData.firmware_version" type="text" placeholder="ex. 16.12.4" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                    </div>
                </div>
            </template>

            {{-- ④ Spécifique routeur --}}
            <template x-if="modalData.type === 'router'">
                <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--success-color);">
                    <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-ethernet"></i> Interfaces</h4>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre total</label><input x-model="formData.interfaces_count" type="number" min="0" placeholder="ex. 24" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Interfaces actives (UP)</label><input x-model="formData.interfaces_up_count" type="number" min="0" placeholder="ex. 22" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--success-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                    </div>
                </div>
            </template>

            {{-- ④ Spécifique firewall --}}
            <template x-if="modalData.type === 'firewall'">
                <div style="background:#fef2f2;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--danger-color);">
                    <h4 style="color:var(--danger-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-shield-alt"></i> Politiques &amp; Performance</h4>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre de règles</label><input x-model="formData.security_policies_count" type="number" min="0" placeholder="ex. 150" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--danger-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">CPU (%)</label><input x-model="formData.cpu" type="number" min="0" max="100" placeholder="ex. 42" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--danger-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                        <div><label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Mémoire (%)</label><input x-model="formData.memory" type="number" min="0" max="100" placeholder="ex. 67" style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;" onfocus="this.style.borderColor='var(--danger-color)'" onblur="this.style.borderColor='var(--border-color)'"></div>
                    </div>
                </div>
            </template>

            {{-- ⑤ Configuration --}}
            <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--info-color);">
                <h4 style="color:var(--info-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;"><i class="fas fa-code"></i> Configuration <span style="font-weight:400;font-size:.85rem;color:var(--text-light);">(optionnel)</span></h4>
                <textarea x-model="formData.configuration" rows="5"
                          :placeholder="modalData.type==='firewall'?'Collez ici la configuration initiale (FortiOS, PAN-OS, etc.)…':'Collez ici la configuration initiale (Cisco IOS, etc.)…'"
                          style="width:100%;padding:12px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;font-size:.88rem;line-height:1.6;resize:vertical;"
                          onfocus="this.style.borderColor='var(--info-color)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
            </div>

            </div></template>{{-- /type !== site --}}

        </div>{{-- /body --}}

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('createEquipmentModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary" @click="saveEquipment()">
                <i class="fas fa-save"></i>
                <span x-text="modalData.id
                    ? 'Enregistrer les modifications'
                    : (modalData.type==='site'    ? 'Créer le site'
                    : (modalData.type==='switch'  ? 'Créer le switch'
                    : (modalData.type==='router'  ? 'Créer le routeur'
                    :                               'Créer le firewall')))">
                </span>
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 2 : DÉTAILS D'ÉQUIPEMENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="equipmentDetailsModal"
     x-show="currentModal === 'view'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:92%;max-width:860px;max-height:92vh;overflow-y:auto;box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">
        <div style="padding:24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,var(--primary-color) 0%,var(--primary-dark) 100%);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.5rem;display:flex;align-items:center;gap:12px;">
                <i class="fas" :class="modalData.type==='switch'?'fa-exchange-alt':modalData.type==='router'?'fa-route':modalData.type==='firewall'?'fa-fire':modalData.type==='site'?'fa-building':'fa-server'"></i>
                <span x-text="modalData.item?.name||'Détails'"></span>
            </h3>
            <button @click="closeModal('equipmentDetailsModal')" style="background:rgba(255,255,255,0.2);border:none;color:white;font-size:1.5rem;width:40px;height:40px;border-radius:50%;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding:24px;" x-html="renderEquipmentDetails()"></div>
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <div style="display:flex;gap:10px;">
                <template x-if="modalData.type==='switch'"><button class="btn btn-outline btn-sm" @click="configurePorts(modalData.item?.id);closeModal('equipmentDetailsModal')"><i class="fas fa-cog"></i> Configurer ports</button></template>
                <template x-if="modalData.type==='router'"><button class="btn btn-outline btn-sm" @click="updateInterfaces(modalData.item?.id);closeModal('equipmentDetailsModal')"><i class="fas fa-ethernet"></i> Interfaces</button></template>
                <template x-if="modalData.type==='firewall'"><button class="btn btn-outline btn-sm" @click="updateSecurityPolicies(modalData.item?.id);closeModal('equipmentDetailsModal')"><i class="fas fa-shield-alt"></i> Politiques</button></template>
            </div>
            <div style="display:flex;gap:12px;">
                <button class="btn btn-outline" @click="closeModal('equipmentDetailsModal')"><i class="fas fa-times"></i> Fermer</button>
                <button class="btn btn-primary" x-show="permissions.create" @click="editEquipment(modalData.type,modalData.item?.id);closeModal('equipmentDetailsModal')"><i class="fas fa-edit"></i> Modifier</button>
            </div>
        </div>
    </div>
</div>


{{-- MODAL 4 : PORTS --}}
<div id="configurePortsModal" x-show="currentModal === 'configurePorts'" x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:1000;display:flex;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:100%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.3);animation:fadeIn .25s ease;">

        {{-- Header --}}
        <div style="padding:20px 24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;position:sticky;top:0;z-index:1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                    <i class="fas fa-network-wired"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:1.2rem;font-weight:700;" x-text="modalTitle"></h3>
                    <p style="margin:2px 0 0;font-size:.8rem;opacity:.8;">Configuration des ports du switch</p>
                </div>
            </div>
            <button @click="closeModal('configurePortsModal')"
                    style="background:rgba(255,255,255,0.15);border:none;color:white;width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1.1rem;transition:background .2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding:24px;display:grid;gap:20px;">

            {{-- Bandeau infos switch --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
                <div style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);padding:14px 16px;border-radius:10px;border:1px solid #6ee7b7;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#059669;" x-text="modalData.item?.ports_total||0"></div>
                    <div style="font-size:.78rem;color:#065f46;font-weight:600;margin-top:2px;">Ports total</div>
                </div>
                <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);padding:14px 16px;border-radius:10px;border:1px solid #93c5fd;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#2563eb;" x-text="modalData.item?.ports_used||0"></div>
                    <div style="font-size:.78rem;color:#1e40af;font-weight:600;margin-top:2px;">Ports utilisés</div>
                </div>
                <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);padding:14px 16px;border-radius:10px;border:1px solid #86efac;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#16a34a;"
                         x-text="(modalData.item?.ports_total||0) - (modalData.item?.ports_used||0)"></div>
                    <div style="font-size:.78rem;color:#15803d;font-weight:600;margin-top:2px;">Ports libres</div>
                </div>
                <div style="background:linear-gradient(135deg,#fafafa,#f4f4f5);padding:14px 16px;border-radius:10px;border:1px solid #d4d4d8;text-align:center;">
                    <div style="font-size:1rem;font-weight:700;color:#3f3f46;" x-text="modalData.item?.site||'N/A'"></div>
                    <div style="font-size:.78rem;color:#71717a;font-weight:600;margin-top:2px;">Site</div>
                </div>
            </div>

            {{-- Barre de progression utilisation --}}
            <div style="background:#f8fafc;padding:16px 20px;border-radius:10px;border:1px solid var(--border-color);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:.85rem;font-weight:600;color:var(--text-light);">Taux d'utilisation</span>
                    <span style="font-size:.9rem;font-weight:700;color:#059669;"
                          x-text="modalData.item?.ports_total ? Math.round((modalData.item.ports_used/modalData.item.ports_total)*100)+'%' : '0%'"></span>
                </div>
                <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
                    <div style="height:100%;border-radius:99px;transition:width .4s ease;background:linear-gradient(90deg,#059669,#10b981);"
                         :style="{ width: modalData.item?.ports_total ? Math.min((modalData.item.ports_used/modalData.item.ports_total)*100, 100)+'%' : '0%' }"></div>
                </div>
            </div>

            {{-- Import fichier --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <h4 style="color:#059669;margin:0 0 14px;display:flex;align-items:center;gap:8px;font-size:.95rem;">
                    <i class="fas fa-file-upload"></i> Importer une configuration
                </h4>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <label style="flex:1;min-width:200px;cursor:pointer;">
                        <div style="padding:10px 14px;border:2px dashed #6ee7b7;border-radius:8px;background:#ecfdf5;text-align:center;color:#059669;font-size:.85rem;font-weight:600;transition:all .2s;"
                             onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='#6ee7b7'">
                            <i class="fas fa-file-code" style="margin-right:6px;"></i>
                            <span id="portConfigFileName">Choisir un fichier (.txt, .json)…</span>
                        </div>
                        <input type="file" accept=".txt,.json,text/plain,application/json" id="portConfigFile" style="display:none;"
                               onchange="document.getElementById('portConfigFileName').textContent = this.files[0]?.name || 'Choisir un fichier (.txt, .json)…'">
                    </label>
                    <button class="btn btn-primary"
                            style="background:linear-gradient(135deg,#059669,#047857);white-space:nowrap;"
                            @click="uploadPortConfig()">
                        <i class="fas fa-upload"></i> Charger
                    </button>
                </div>
                <p style="margin:10px 0 0;font-size:.78rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i> Formats acceptés : <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">.txt</code> (config IOS/HP) ou <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">.json</code> — ex. <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">[{"port": 1, "vlan": 10}]</code>
                </p>
            </div>

            {{-- Configuration actuelle --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h4 style="color:#059669;margin:0;display:flex;align-items:center;gap:8px;font-size:.95rem;">
                        <i class="fas fa-code"></i> Configuration chargée
                    </h4>
                    <button x-show="formData.portConfiguration"
                            @click="formData.portConfiguration = ''"
                            style="background:none;border:none;color:var(--danger-color);cursor:pointer;font-size:.8rem;font-weight:600;padding:4px 8px;border-radius:6px;border:1px solid var(--danger-color);">
                        <i class="fas fa-trash-alt"></i> Effacer
                    </button>
                </div>
                <pre x-show="formData.portConfiguration"
                     style="background:white;padding:14px;border:1px solid #d1fae5;border-radius:8px;font-family:'JetBrains Mono',monospace,monospace;font-size:.82rem;overflow-x:auto;white-space:pre-wrap;max-height:240px;color:#065f46;line-height:1.5;"
                     x-text="formData.portConfiguration"></pre>
                <div x-show="!formData.portConfiguration"
                     style="padding:32px;text-align:center;color:var(--text-light);border:2px dashed var(--border-color);border-radius:8px;background:white;">
                    <i class="fas fa-file-code" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                    Aucune configuration chargée
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <span style="font-size:.8rem;color:var(--text-light);">
                <i class="fas fa-info-circle"></i> Les modifications seront appliquées immédiatement sur le switch
            </span>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-outline" @click="closeModal('configurePortsModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button class="btn btn-primary"
                        style="background:linear-gradient(135deg,#059669,#047857);"
                        :disabled="!formData.portConfiguration"
                        :style="{ opacity: formData.portConfiguration ? 1 : 0.5 }"
                        @click="savePortConfiguration()">
                    <i class="fas fa-save"></i> Appliquer la configuration
                </button>
            </div>
        </div>
    </div>
</div>


{{-- MODAL 5 : INTERFACES --}}
<div id="updateInterfacesModal" x-show="currentModal === 'updateInterfaces'" x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:1000;display:flex;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:100%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.3);animation:fadeIn .25s ease;">

        {{-- Header --}}
        <div style="padding:20px 24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#0891b2,#0e7490);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;position:sticky;top:0;z-index:1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:1.2rem;font-weight:700;" x-text="modalTitle"></h3>
                    <p style="margin:2px 0 0;font-size:.8rem;opacity:.8;">Configuration du routeur</p>
                </div>
            </div>
            <button @click="closeModal('updateInterfacesModal')"
                    style="background:rgba(255,255,255,0.15);border:none;color:white;width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1.1rem;transition:background .2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding:24px;display:grid;gap:20px;">

            {{-- Stats routeur --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
                <div style="background:linear-gradient(135deg,#ecfeff,#cffafe);padding:14px 16px;border-radius:10px;border:1px solid #67e8f9;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#0891b2;" x-text="modalData.item?.interfaces_count||0"></div>
                    <div style="font-size:.78rem;color:#164e63;font-weight:600;margin-top:2px;">Total interfaces</div>
                </div>
                <div style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);padding:14px 16px;border-radius:10px;border:1px solid #6ee7b7;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#059669;" x-text="modalData.item?.interfaces_up_count||0"></div>
                    <div style="font-size:.78rem;color:#065f46;font-weight:600;margin-top:2px;">Interfaces UP</div>
                </div>
                <div style="background:linear-gradient(135deg,#fef2f2,#fee2e2);padding:14px 16px;border-radius:10px;border:1px solid #fca5a5;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#dc2626;"
                         x-text="(modalData.item?.interfaces_count||0) - (modalData.item?.interfaces_up_count||0)"></div>
                    <div style="font-size:.78rem;color:#991b1b;font-weight:600;margin-top:2px;">Interfaces DOWN</div>
                </div>
                <div style="background:linear-gradient(135deg,#fafafa,#f4f4f5);padding:14px 16px;border-radius:10px;border:1px solid #d4d4d8;text-align:center;">
                    <div style="font-size:.95rem;font-weight:700;color:#3f3f46;" x-text="modalData.item?.site||'N/A'"></div>
                    <div style="font-size:.78rem;color:#71717a;font-weight:600;margin-top:2px;">Site</div>
                </div>
            </div>

            {{-- Import fichier .txt ou .json --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <h4 style="color:#0891b2;margin:0 0 14px;display:flex;align-items:center;gap:8px;font-size:.95rem;">
                    <i class="fas fa-file-upload"></i> Importer un fichier de configuration
                </h4>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <label style="flex:1;min-width:200px;cursor:pointer;">
                        <div style="padding:10px 14px;border:2px dashed #67e8f9;border-radius:8px;background:#ecfeff;text-align:center;color:#0891b2;font-size:.85rem;font-weight:600;transition:all .2s;"
                             onmouseover="this.style.borderColor='#0891b2'" onmouseout="this.style.borderColor='#67e8f9'">
                            <i class="fas fa-file-code" style="margin-right:6px;"></i>
                            <span id="routerConfigFileName">Choisir un fichier (.txt, .json)…</span>
                        </div>
                        <input type="file" accept=".txt,.json,text/plain,application/json"
                               id="routerConfigFile" style="display:none;"
                               onchange="document.getElementById('routerConfigFileName').textContent = this.files[0]?.name || 'Choisir un fichier (.txt, .json)…'">
                    </label>
                    <button class="btn btn-primary"
                            style="background:linear-gradient(135deg,#0891b2,#0e7490);white-space:nowrap;"
                            @click="uploadRouterConfig()">
                        <i class="fas fa-upload"></i> Charger
                    </button>
                </div>
                <p style="margin:10px 0 0;font-size:.78rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i>
                    Formats acceptés : <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">.txt</code> (Cisco IOS, etc.) ou
                    <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">.json</code>
                </p>
            </div>

            {{-- Saisie / affichage configuration --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h4 style="color:#0891b2;margin:0;font-size:.95rem;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-code"></i> Configuration
                        <span x-show="formData.routerConfigType"
                              style="font-size:.75rem;font-weight:500;padding:2px 8px;border-radius:20px;background:#cffafe;color:#0e7490;"
                              x-text="formData.routerConfigType === 'json' ? '● JSON' : '● Texte'"></span>
                    </h4>
                    <button x-show="formData.interfacesConfig"
                            @click="formData.interfacesConfig = ''; formData.routerConfigType = ''"
                            style="background:none;border:none;color:var(--danger-color);cursor:pointer;font-size:.8rem;font-weight:600;padding:4px 8px;border-radius:6px;border:1px solid var(--danger-color);">
                        <i class="fas fa-eraser"></i> Vider
                    </button>
                </div>
                <textarea x-model="formData.interfacesConfig" rows="12"
                          placeholder="Collez ici la configuration ou importez un fichier…&#10;&#10;Exemples :&#10;  - Cisco IOS : interface GigabitEthernet0/0 / ip address 192.168.1.1 255.255.255.0&#10;  - JSON      : [{&quot;interface&quot;:&quot;Gi0/0&quot;,&quot;status&quot;:&quot;up&quot;,&quot;ip&quot;:&quot;192.168.1.1&quot;}]"
                          style="width:100%;padding:12px 14px;border:2px solid var(--border-color);border-radius:8px;font-family:'JetBrains Mono',monospace,monospace;font-size:.82rem;resize:vertical;line-height:1.6;transition:border-color .2s;"
                          onfocus="this.style.borderColor='#0891b2'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <span style="font-size:.8rem;color:var(--text-light);">
                <i class="fas fa-exclamation-triangle" style="color:var(--warning-color);"></i> Vérifier la configuration avant d'appliquer
            </span>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-outline" @click="closeModal('updateInterfacesModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button class="btn btn-primary"
                        style="background:linear-gradient(135deg,#0891b2,#0e7490);"
                        :disabled="!formData.interfacesConfig"
                        :style="{ opacity: formData.interfacesConfig ? 1 : 0.5 }"
                        @click="saveInterfacesUpdate()">
                    <i class="fas fa-save"></i> Appliquer la configuration
                </button>
            </div>
        </div>
    </div>
</div>


{{-- MODAL 6 : POLITIQUES --}}
<div id="updateSecurityPoliciesModal" x-show="currentModal === 'updateSecurityPolicies'" x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:1000;display:flex;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:100%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.3);animation:fadeIn .25s ease;">

        {{-- Header --}}
        <div style="padding:20px 24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#dc2626,#b91c1c);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;position:sticky;top:0;z-index:1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:1.2rem;font-weight:700;" x-text="modalTitle"></h3>
                    <p style="margin:2px 0 0;font-size:.8rem;opacity:.8;">Gestion des politiques de sécurité</p>
                </div>
            </div>
            <button @click="closeModal('updateSecurityPoliciesModal')"
                    style="background:rgba(255,255,255,0.15);border:none;color:white;width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1.1rem;transition:background .2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding:24px;display:grid;gap:20px;">

            {{-- Stats firewall --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">
                <div style="background:linear-gradient(135deg,#fef2f2,#fee2e2);padding:14px 16px;border-radius:10px;border:1px solid #fca5a5;text-align:center;">
                    <div style="font-size:1.6rem;font-weight:800;color:#dc2626;" x-text="modalData.item?.security_policies_count||0"></div>
                    <div style="font-size:.78rem;color:#991b1b;font-weight:600;margin-top:2px;">Règles actives</div>
                </div>
                <div style="background:linear-gradient(135deg,#fff7ed,#fed7aa);padding:14px 16px;border-radius:10px;border:1px solid #fdba74;text-align:center;">
                    <div style="font-size:1rem;font-weight:700;color:#ea580c;" x-text="modalData.item?.brand||'N/A'"></div>
                    <div style="font-size:.78rem;color:#9a3412;font-weight:600;margin-top:2px;">Fabricant</div>
                </div>
                <div style="background:linear-gradient(135deg,#fafafa,#f4f4f5);padding:14px 16px;border-radius:10px;border:1px solid #d4d4d8;text-align:center;">
                    <div style="font-size:.95rem;font-weight:700;color:#3f3f46;" x-text="modalData.item?.site||'N/A'"></div>
                    <div style="font-size:.78rem;color:#71717a;font-weight:600;margin-top:2px;">Site</div>
                </div>
                <div style="padding:14px 16px;border-radius:10px;text-align:center;border:1px solid;"
                     :style="{ background: modalData.item?.status==='active' ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fef2f2,#fee2e2)', borderColor: modalData.item?.status==='active' ? '#6ee7b7' : '#fca5a5' }">
                    <div style="font-size:.9rem;font-weight:700;"
                         :style="{ color: modalData.item?.status==='active' ? '#059669' : '#dc2626' }"
                         x-text="modalData.item?.status==='active' ? '✓ Actif' : '✕ Inactif'"></div>
                    <div style="font-size:.78rem;font-weight:600;margin-top:2px;color:#6b7280;">Statut</div>
                </div>
            </div>

            {{-- Alerte sécurité --}}
            <div style="background:#fef9c3;padding:12px 16px;border-radius:8px;border-left:4px solid #eab308;display:flex;align-items:flex-start;gap:10px;">
                <i class="fas fa-exclamation-triangle" style="color:#ca8a04;margin-top:2px;flex-shrink:0;"></i>
                <p style="margin:0;font-size:.82rem;color:#713f12;line-height:1.5;">
                    <strong>Attention :</strong> La modification des politiques de sécurité peut impacter le trafic réseau en production.
                    Assurez-vous de valider votre configuration avant d'appliquer.
                </p>
            </div>

            {{-- Import fichier --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <h4 style="color:#dc2626;margin:0 0 14px;display:flex;align-items:center;gap:8px;font-size:.95rem;">
                    <i class="fas fa-file-upload"></i> Importer un fichier de politiques
                </h4>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <label style="flex:1;min-width:200px;cursor:pointer;">
                        <div style="padding:10px 14px;border:2px dashed #fca5a5;border-radius:8px;background:#fef2f2;text-align:center;color:#dc2626;font-size:.85rem;font-weight:600;transition:all .2s;"
                             onmouseover="this.style.borderColor='#dc2626'" onmouseout="this.style.borderColor='#fca5a5'">
                            <i class="fas fa-file-shield" style="margin-right:6px;"></i>
                            <span id="secPolFileName">Choisir un fichier (.txt, .json)…</span>
                        </div>
                        <input type="file" accept=".txt,.json,text/plain,application/json" id="securityPoliciesFile" style="display:none;"
                               onchange="document.getElementById('secPolFileName').textContent = this.files[0]?.name || 'Choisir un fichier (.txt, .json)…'">
                    </label>
                    <button class="btn btn-primary"
                            style="background:linear-gradient(135deg,#dc2626,#b91c1c);white-space:nowrap;"
                            @click="uploadSecurityPolicies()">
                        <i class="fas fa-upload"></i> Charger
                    </button>
                </div>
            </div>

            {{-- Politiques actuelles --}}
            <div style="background:#f8fafc;padding:20px;border-radius:10px;border:1px solid var(--border-color);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h4 style="color:#dc2626;margin:0;font-size:.95rem;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-list-alt"></i> Politiques chargées
                    </h4>
                    <button x-show="formData.securityPolicies"
                            @click="formData.securityPolicies = ''"
                            style="background:none;border:none;color:var(--danger-color);cursor:pointer;font-size:.8rem;font-weight:600;padding:4px 8px;border-radius:6px;border:1px solid var(--danger-color);">
                        <i class="fas fa-trash-alt"></i> Effacer
                    </button>
                </div>
                <pre x-show="formData.securityPolicies"
                     style="background:white;padding:14px;border:1px solid #fecaca;border-radius:8px;font-family:'JetBrains Mono',monospace,monospace;font-size:.82rem;overflow-x:auto;white-space:pre-wrap;max-height:260px;color:#7f1d1d;line-height:1.5;"
                     x-text="formData.securityPolicies"></pre>
                <div x-show="!formData.securityPolicies"
                     style="padding:32px;text-align:center;color:var(--text-light);border:2px dashed var(--border-color);border-radius:8px;background:white;">
                    <i class="fas fa-shield-alt" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                    Aucune politique chargée
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <span style="font-size:.8rem;color:var(--text-light);">
                <i class="fas fa-lock" style="color:#dc2626;"></i> Action sensible — impact sur le trafic réseau
            </span>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-outline" @click="closeModal('updateSecurityPoliciesModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button class="btn btn-primary"
                        style="background:linear-gradient(135deg,#dc2626,#b91c1c);"
                        :disabled="!formData.securityPolicies"
                        :style="{ opacity: formData.securityPolicies ? 1 : 0.5 }"
                        @click="saveSecurityPolicies()">
                    <i class="fas fa-save"></i> Appliquer les politiques
                </button>
            </div>
        </div>
    </div>
</div>


{{-- MODAL 7 : TOGGLE USER STATUS --}}
<div id="toggleUserStatusModal" x-show="currentModal === 'toggleUserStatus'" x-cloak style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:1000;display:flex;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:92%;max-width:500px;box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">
        <div style="padding:24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,var(--warning-color),#d97706);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;"><i class="fas fa-toggle-on"></i> Confirmation</h3>
            <button @click="closeModal('toggleUserStatusModal')" style="background:rgba(255,255,255,0.2);border:none;color:white;font-size:1.5rem;width:40px;height:40px;border-radius:50%;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding:24px;text-align:center;">
            <div style="font-size:3rem;color:var(--warning-color);margin-bottom:16px;"><i class="fas" :class="userToToggle?.is_active?'fa-toggle-off':'fa-toggle-on'"></i></div>
            <p style="font-size:1.1rem;margin-bottom:8px;">Êtes-vous sûr de vouloir <strong x-text="userToToggle?.is_active?'désactiver':'activer'"></strong> l'utilisateur</p>
            <p style="font-size:1.3rem;font-weight:700;color:var(--primary-color);margin-bottom:16px;" x-text="userToToggle?.name"></p>
            <p style="color:var(--text-light);font-size:.9rem;">Cette action modifiera ses permissions d'accès à la plateforme.</p>
        </div>
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);display:flex;justify-content:flex-end;gap:12px;background:#f8fafc;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('toggleUserStatusModal')"><i class="fas fa-times"></i> Annuler</button>
            <button class="btn btn-primary" style="background:linear-gradient(135deg,var(--warning-color),#d97706);" @click="confirmToggleUserStatus()"><i class="fas fa-check"></i> Confirmer</button>
        </div>
    </div>
</div>


{{-- MODAL SUPPRESSION --}}
<div id="confirmDeleteModal"
     x-show="currentModal === 'confirmDelete'" x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.65);z-index:1100;display:flex;align-items:center;justify-content:center;padding:16px;">
    <div style="background:white;border-radius:var(--border-radius-lg);width:100%;max-width:480px;box-shadow:0 25px 60px rgba(0,0,0,0.35);animation:fadeIn .2s ease;"
         @click.stop>

        {{-- Header rouge --}}
        <div style="padding:20px 24px;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#dc2626,#b91c1c);color:white;border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 style="margin:0;font-size:1.1rem;font-weight:700;">Confirmer la suppression</h3>
            </div>
            <button @click="closeModal('confirmDeleteModal')"
                    style="background:rgba(255,255,255,0.15);border:none;color:white;width:34px;height:34px;border-radius:8px;cursor:pointer;font-size:1rem;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Corps --}}
        <div style="padding:28px 24px;text-align:center;">
            <div style="width:64px;height:64px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;color:#dc2626;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p style="font-size:1rem;color:#374151;margin:0 0 8px;">
                Êtes-vous sûr de vouloir supprimer le
                <strong x-text="deleteTarget?.label"></strong>
            </p>
            <p style="font-size:1.2rem;font-weight:700;color:#111827;margin:0 0 16px;"
               x-text="deleteTarget?.name"></p>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-exclamation-circle" style="color:#dc2626;flex-shrink:0;"></i>
                <span style="font-size:.82rem;color:#991b1b;">Cette action est <strong>irréversible</strong>. Toutes les données associées seront perdues.</span>
            </div>
        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px;background:#f9fafb;border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('confirmDeleteModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary"
                    style="background:linear-gradient(135deg,#dc2626,#b91c1c);"
                    @click="confirmDelete()">
                <i class="fas fa-trash-alt"></i> Supprimer définitivement
            </button>
        </div>
    </div>
</div>