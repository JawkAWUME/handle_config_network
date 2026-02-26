{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  dashboard/partials/modals.blade.php                                ║
    ║  SOURCE UNIQUE de vérité pour TOUS les modaux de l'application.     ║
    ╚══════════════════════════════════════════════════════════════════════╝

    DESIGN SYSTEM — règles communes à tous les modaux :
    ─────────────────────────────────────────────────────
    • Overlay    : rgba(0,0,0,0.55) · z-index 1000
    • Conteneur  : border-radius-lg · max-height 92vh · overflow-y auto
    • Header     : gradient primary · couleur blanche · bouton ✕ rond
    • Body       : padding 24px · sections colorées par thème
    • Footer     : #f8fafc · border-top · boutons à droite
    • Animation  : fadeIn .3s ease
    • IDs unifiés :
        - createEquipmentModal       (create / edit — site | user | switch | router | firewall)
        - equipmentDetailsModal      (détails — tous types)
        - testConnectivityModal      (test — tous types, couleur dynamique)
        - configurePortsModal        (switch uniquement)
        - updateInterfacesModal      (router uniquement)
        - updateSecurityPoliciesModal(firewall uniquement)
--}}


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 1 : CRÉATION / ÉDITION (site, user, switch, router, firewall)
     Condition : currentModal === 'create'
     Type discriminant : modalData.type = 'site' | 'user' | 'switch' | 'router' | 'firewall'
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

        {{-- ── HEADER (commun) ─────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid var(--border-color);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,var(--primary-color) 0%,var(--primary-dark) 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.5rem;display:flex;align-items:center;gap:12px;">
                {{-- Icône contextuelle --}}
                <i class="fas"
                   :class="modalData.type==='site'     ? 'fa-building'
                          :modalData.type==='user'     ? 'fa-user'
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

        {{-- ── BODY conditionnel ──────────────────────────────────────── --}}
        <div style="padding:24px;display:grid;gap:24px;">

            {{-- ############### FORMULAIRE SITE ############### --}}
            <template x-if="modalData.type === 'site'">
                <div style="display:grid;gap:24px;">
                    {{-- 1. Informations générales --}}
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--primary-color);">
                        <h4 style="color:var(--primary-color);margin:0 0 16px;">
                            <i class="fas fa-info-circle"></i> Informations générales
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Nom <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.name" type="text" placeholder="ex. Siège Social Paris"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Code <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.code" type="text" placeholder="ex. PAR-HQ"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div style="grid-column:1/-1;">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Description</label>
                                <textarea x-model="formData.description" rows="3" placeholder="Description du site..."
                                          style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);resize:vertical;"
                                          onfocus="this.style.borderColor='var(--primary-color)'"
                                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Localisation --}}
                    <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--success-color);">
                        <h4 style="color:var(--success-color);margin:0 0 16px;">
                            <i class="fas fa-map-marker-alt"></i> Localisation
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div style="grid-column:1/-1;">
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Adresse</label>
                                <input x-model="formData.address" type="text" placeholder="ex. 123 Avenue des Champs-Élysées"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Code postal</label>
                                <input x-model="formData.postal_code" type="text" placeholder="ex. 75008"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ville</label>
                                <input x-model="formData.city" type="text" placeholder="ex. Paris"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Région</label>
                                <input x-model="formData.region" type="text" placeholder="ex. Île-de-France"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Pays</label>
                                <input x-model="formData.country" type="text" placeholder="ex. France"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--success-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                        </div>
                    </div>

                    {{-- 3. Coordonnées GPS (optionnel) --}}
                    <div style="background:#e0f2fe;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--info-color);">
                        <h4 style="color:var(--info-color);margin:0 0 16px;">
                            <i class="fas fa-globe"></i> Coordonnées GPS
                            <span style="font-weight:400;font-size:.85rem;color:var(--text-light);">(optionnel)</span>
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Latitude</label>
                                <input x-model="formData.latitude" type="number" step="0.000001" placeholder="ex. 48.8566"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--info-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Longitude</label>
                                <input x-model="formData.longitude" type="number" step="0.000001" placeholder="ex. 2.3522"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--info-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                        </div>
                    </div>

                    {{-- 4. Informations de contact --}}
                    <div style="background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);
                                padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--warning-color);">
                        <h4 style="color:#92400e;margin:0 0 16px;">
                            <i class="fas fa-address-book"></i> Informations de contact
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-user"></i> Nom du contact
                                </label>
                                <input x-model="formData.contact_name" type="text" placeholder="ex. Jean Dupont"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;"
                                       onfocus="this.style.borderColor='#92400e'"
                                       onblur="this.style.borderColor='#f59e0b'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input x-model="formData.contact_email" type="email" placeholder="ex. contact@site.fr"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                       onfocus="this.style.borderColor='#92400e'"
                                       onblur="this.style.borderColor='#f59e0b'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-phone"></i> Téléphone
                                </label>
                                <input x-model="formData.contact_phone" type="tel" placeholder="ex. +33 1 23 45 67 89"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                       onfocus="this.style.borderColor='#92400e'"
                                       onblur="this.style.borderColor='#f59e0b'">
                            </div>
                        </div>
                    </div>

                    {{-- 5. Équipements associés au site --}}
                    <template x-if="modalData.id">
                        <div style="background:#e0f2fe;padding:20px;border-radius:var(--border-radius);
                                    border-left:4px solid var(--info-color);">
                            <h4 style="color:var(--info-color);margin:0 0 16px;">
                                <i class="fas fa-network-wired"></i> Équipements associés
                            </h4>

                            {{-- Switchs --}}
                            <div style="margin-bottom:16px;">
                                <div style="font-weight:600;color:var(--text-color);margin-bottom:8px;
                                            display:flex;justify-content:space-between;align-items:center;">
                                    <span><i class="fas fa-exchange-alt" style="color:var(--primary-color);"></i> Switchs</span>
                                    <button class="btn btn-primary btn-sm"
                                            @click="closeModal('createEquipmentModal'); openCreateModal('switch'); formData.site_id = modalData.id">
                                        <i class="fas fa-plus"></i> Ajouter
                                    </button>
                                </div>
                                <div style="display:grid;gap:8px;">
                                    <template x-for="sw in switches.filter(s => s.site_id == modalData.id)" :key="sw.id">
                                        <div style="background:white;padding:10px 14px;border-radius:8px;
                                                    display:flex;justify-content:space-between;align-items:center;
                                                    border:1px solid var(--border-color);">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <i class="fas fa-exchange-alt" style="color:var(--primary-color);"></i>
                                                <span style="font-weight:600;" x-text="sw.name"></span>
                                                <span class="status-badge"
                                                    :class="sw.status === 'active' ? 'status-active' : 'status-danger'"
                                                    x-text="sw.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>
                                            <div style="display:flex;gap:6px;">
                                                <button class="btn btn-outline btn-sm"
                                                        @click="editEquipment('switch', sw.id)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm"
                                                        style="color:var(--danger-color);border-color:var(--danger-color);"
                                                        @click="deleteItem('switches', sw.id)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="switches.filter(s => s.site_id == modalData.id).length === 0"
                                        style="color:var(--text-light);font-size:.9rem;text-align:center;padding:8px;">
                                        <i class="fas fa-info-circle"></i> Aucun switch associé
                                    </div>
                                </div>
                            </div>

                            {{-- Routeurs --}}
                            <div style="margin-bottom:16px;">
                                <div style="font-weight:600;color:var(--text-color);margin-bottom:8px;
                                            display:flex;justify-content:space-between;align-items:center;">
                                    <span><i class="fas fa-route" style="color:var(--success-color);"></i> Routeurs</span>
                                    <button class="btn btn-primary btn-sm"
                                            @click="closeModal('createEquipmentModal'); openCreateModal('router'); formData.site_id = modalData.id">
                                        <i class="fas fa-plus"></i> Ajouter
                                    </button>
                                </div>
                                <div style="display:grid;gap:8px;">
                                    <template x-for="rt in routers.filter(r => r.site_id == modalData.id)" :key="rt.id">
                                        <div style="background:white;padding:10px 14px;border-radius:8px;
                                                    display:flex;justify-content:space-between;align-items:center;
                                                    border:1px solid var(--border-color);">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <i class="fas fa-route" style="color:var(--success-color);"></i>
                                                <span style="font-weight:600;" x-text="rt.name"></span>
                                                <span class="status-badge"
                                                    :class="rt.status === 'active' ? 'status-active' : 'status-danger'"
                                                    x-text="rt.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>
                                            <div style="display:flex;gap:6px;">
                                                <button class="btn btn-outline btn-sm"
                                                        @click="editEquipment('router', rt.id)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm"
                                                        style="color:var(--danger-color);border-color:var(--danger-color);"
                                                        @click="deleteItem('routers', rt.id)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="routers.filter(r => r.site_id == modalData.id).length === 0"
                                        style="color:var(--text-light);font-size:.9rem;text-align:center;padding:8px;">
                                        <i class="fas fa-info-circle"></i> Aucun routeur associé
                                    </div>
                                </div>
                            </div>

                            {{-- Firewalls --}}
                            <div>
                                <div style="font-weight:600;color:var(--text-color);margin-bottom:8px;
                                            display:flex;justify-content:space-between;align-items:center;">
                                    <span><i class="fas fa-fire" style="color:var(--danger-color);"></i> Firewalls</span>
                                    <button class="btn btn-primary btn-sm"
                                            @click="closeModal('createEquipmentModal'); openCreateModal('firewall'); formData.site_id = modalData.id">
                                        <i class="fas fa-plus"></i> Ajouter
                                    </button>
                                </div>
                                <div style="display:grid;gap:8px;">
                                    <template x-for="fw in firewalls.filter(f => f.site_id == modalData.id)" :key="fw.id">
                                        <div style="background:white;padding:10px 14px;border-radius:8px;
                                                    display:flex;justify-content:space-between;align-items:center;
                                                    border:1px solid var(--border-color);">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <i class="fas fa-fire" style="color:var(--danger-color);"></i>
                                                <span style="font-weight:600;" x-text="fw.name"></span>
                                                <span class="status-badge"
                                                    :class="fw.status === 'active' ? 'status-active' : 'status-danger'"
                                                    x-text="fw.status === 'active' ? 'Actif' : 'Inactif'"></span>
                                            </div>
                                            <div style="display:flex;gap:6px;">
                                                <button class="btn btn-outline btn-sm"
                                                        @click="editEquipment('firewall', fw.id)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm"
                                                        style="color:var(--danger-color);border-color:var(--danger-color);"
                                                        @click="deleteItem('firewalls', fw.id)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="firewalls.filter(f => f.site_id == modalData.id).length === 0"
                                        style="color:var(--text-light);font-size:.9rem;text-align:center;padding:8px;">
                                        <i class="fas fa-info-circle"></i> Aucun firewall associé
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- 6. Notes additionnelles --}}
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--accent-color);">
                        <h4 style="color:var(--accent-color);margin:0 0 16px;">
                            <i class="fas fa-sticky-note"></i> Notes additionnelles
                            <span style="font-weight:400;font-size:.85rem;color:var(--text-light);">(optionnel)</span>
                        </h4>
                        <textarea x-model="formData.notes" rows="4" placeholder="Informations complémentaires..."
                                  style="width:100%;padding:12px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);resize:vertical;"
                                  onfocus="this.style.borderColor='var(--accent-color)'"
                                  onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>
                </div>
            </template>

            {{-- ############### FORMULAIRE UTILISATEUR ############### --}}
            <template x-if="modalData.type === 'user'">
                <div style="display:grid;gap:24px;">
                    {{-- Informations de base --}}
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--primary-color);">
                        <h4 style="color:var(--primary-color);margin:0 0 16px;">
                            <i class="fas fa-user"></i> Informations de base
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Nom complet <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.name" type="text" placeholder="ex. Jean Dupont"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Email <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.email" type="email" placeholder="ex. jean@exemple.com"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Rôle <span style="color:var(--danger-color);">*</span>
                                </label>
                                <select x-model="formData.role"
                                        style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);background:white;"
                                        onfocus="this.style.borderColor='var(--primary-color)'"
                                        onblur="this.style.borderColor='var(--border-color)'">
                                    <option value="admin">Administrateur</option>
                                    <option value="technician">Technicien</option>
                                    <option value="viewer">Observateur</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Département</label>
                                <input x-model="formData.department" type="text" placeholder="ex. IT"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Téléphone</label>
                                <input x-model="formData.phone" type="text" placeholder="ex. +33 6 12 34 56 78"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);border-radius:var(--border-radius);font-family:monospace;"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Statut</label>
                                <div style="display:flex;gap:12px;align-items:center;padding-top:6px;">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                        <input type="radio" x-model="formData.is_active" :value="true"
                                               style="accent-color:var(--success-color);width:16px;height:16px;">
                                        <span style="color:var(--success-color);"><i class="fas fa-check-circle"></i> Actif</span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                        <input type="radio" x-model="formData.is_active" :value="false"
                                               style="accent-color:var(--danger-color);width:16px;height:16px;">
                                        <span style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> Inactif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mot de passe (affiché seulement en création ou si on veut le changer) --}}
                    <div style="background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);
                                padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--warning-color);">
                        <h4 style="color:#92400e;margin:0 0 16px;">
                            <i class="fas fa-key"></i> Mot de passe
                        </h4>
                        <p style="margin-bottom:12px;font-size:.9rem;color:#92400e;">
                            <i class="fas fa-info-circle"></i>
                            <span x-text="modalData.id ? 'Laissez vide pour conserver le mot de passe actuel.' : 'Choisissez un mot de passe pour le nouvel utilisateur.'"></span>
                        </p>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                                <div style="position:relative;">
                                    <input x-model="formData.password"
                                           :type="formData._showPass ? 'text' : 'password'"
                                           placeholder="••••••••••••"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                           onfocus="this.style.borderColor='#92400e'"
                                           onblur="this.style.borderColor='#f59e0b'">
                                    <button type="button" @click="formData._showPass = !formData._showPass"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                   background:none;border:none;cursor:pointer;color:#92400e;">
                                        <i class="fas" :class="formData._showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-redo-alt"></i> Confirmation
                                </label>
                                <div style="position:relative;">
                                    <input x-model="formData.password_confirmation"
                                           :type="formData._showPassConfirm ? 'text' : 'password'"
                                           placeholder="••••••••••••"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;border-radius:var(--border-radius);background:white;font-family:monospace;"
                                           onfocus="this.style.borderColor='#92400e'"
                                           onblur="this.style.borderColor='#f59e0b'">
                                    <button type="button" @click="formData._showPassConfirm = !formData._showPassConfirm"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                   background:none;border:none;cursor:pointer;color:#92400e;">
                                        <i class="fas" :class="formData._showPassConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ############### FORMULAIRE ÉQUIPEMENT (switch, router, firewall) ############### --}}
            <template x-if="modalData.type !== 'site' && modalData.type !== 'user'">
                <div style="display:grid;gap:24px;">
                    {{-- ① Informations générales (commun à tous) --}}
                    <div style="background:#f8fafc;padding:20px;
                                border-radius:var(--border-radius);
                                border-left:4px solid var(--primary-color);">
                        <h4 style="color:var(--primary-color);margin:0 0 16px;
                                   display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-info-circle"></i> Informations générales
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Nom <span style="color:var(--danger-color);">*</span>
                                </label>
                                <input x-model="formData.name" type="text"
                                       :placeholder="modalData.type==='switch'   ? 'ex. SW-PARIS-01'
                                                    :modalData.type==='router'   ? 'ex. RT-PARIS-01'
                                                    :modalData.type==='firewall' ? 'ex. FW-PARIS-01'
                                                    :'Nom de l\'équipement'"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:var(--font-secondary);
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">
                                    Site <span style="color:var(--danger-color);">*</span>
                                </label>
                                <select x-model="formData.site_id"
                                        style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                               border-radius:var(--border-radius);font-family:var(--font-secondary);
                                               font-size:.95rem;background:white;transition:var(--transition);"
                                        onfocus="this.style.borderColor='var(--primary-color)'"
                                        onblur="this.style.borderColor='var(--border-color)'">
                                    <option value="">— Sélectionner un site —</option>
                                    <template x-for="site in sites" :key="site.id">
                                        <option :value="site.id" x-text="site.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Marque</label>
                                <input x-model="formData.brand" type="text"
                                       :placeholder="modalData.type==='firewall' ? 'ex. Fortinet, Palo Alto…'
                                                    :modalData.type==='router'   ? 'ex. Cisco, Juniper…'
                                                    :'ex. Cisco, HP, Dell…'"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:var(--font-secondary);
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Modèle</label>
                                <input x-model="formData.model" type="text"
                                       :placeholder="modalData.type==='firewall' ? 'ex. FortiGate 100F'
                                                    :modalData.type==='router'   ? 'ex. Cisco ISR 4451'
                                                    :'ex. Catalyst 9300'"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:var(--font-secondary);
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Numéro de série</label>
                                <input x-model="formData.serial_number" type="text"
                                       :placeholder="modalData.type==='firewall' ? 'ex. FG1H0E3919000000'
                                                    :modalData.type==='router'   ? 'ex. FTX1234ABCD'
                                                    :'ex. FDO2049Z0CL'"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:monospace;
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--primary-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Statut</label>
                                <div style="display:flex;gap:12px;align-items:center;padding-top:6px;">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                        <input type="radio" x-model="formData.status" value="active"
                                               style="accent-color:var(--success-color);width:16px;height:16px;">
                                        <span style="color:var(--success-color);"><i class="fas fa-check-circle"></i> Actif</span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                                        <input type="radio" x-model="formData.status" value="danger"
                                               style="accent-color:var(--danger-color);width:16px;height:16px;">
                                        <span style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> Inactif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ② Credentials d'accès (commun à tous) --}}
                    <div style="background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);
                                padding:20px;border-radius:var(--border-radius);
                                border-left:4px solid var(--warning-color);">
                        <h4 style="color:#92400e;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-key"></i> Credentials d'accès
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-user-shield"></i> Nom d'utilisateur
                                </label>
                                <input x-model="formData.username" type="text" placeholder="ex. admin"
                                       style="width:100%;padding:10px 14px;border:2px solid #f59e0b;
                                              border-radius:var(--border-radius);background:white;
                                              font-family:monospace;font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='#92400e'"
                                       onblur="this.style.borderColor='#f59e0b'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-lock"></i> Mot de passe
                                </label>
                                <div style="position:relative;">
                                    <input x-model="formData.password"
                                           :type="formData._showPass ? 'text' : 'password'"
                                           placeholder="••••••••••••"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;
                                                  border-radius:var(--border-radius);background:white;
                                                  font-family:monospace;font-size:.95rem;transition:var(--transition);"
                                           onfocus="this.style.borderColor='#92400e'"
                                           onblur="this.style.borderColor='#f59e0b'">
                                    <button type="button" @click="formData._showPass = !formData._showPass"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                   background:none;border:none;cursor:pointer;color:#92400e;">
                                        <i class="fas" :class="formData._showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:#92400e;display:block;margin-bottom:6px;font-weight:600;">
                                    <i class="fas fa-shield-alt"></i> Enable Password
                                    <span style="font-weight:400;font-size:.8rem;">(optionnel)</span>
                                </label>
                                <div style="position:relative;">
                                    <input x-model="formData.enable_password"
                                           :type="formData._showEnablePass ? 'text' : 'password'"
                                           placeholder="••••••••••••"
                                           style="width:100%;padding:10px 40px 10px 14px;border:2px solid #f59e0b;
                                                  border-radius:var(--border-radius);background:white;
                                                  font-family:monospace;font-size:.95rem;transition:var(--transition);"
                                           onfocus="this.style.borderColor='#92400e'"
                                           onblur="this.style.borderColor='#f59e0b'">
                                    <button type="button" @click="formData._showEnablePass = !formData._showEnablePass"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                   background:none;border:none;cursor:pointer;color:#92400e;">
                                        <i class="fas" :class="formData._showEnablePass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ③ Configuration réseau (commun à tous) --}}
                    <div style="background:#f8fafc;padding:20px;
                                border-radius:var(--border-radius);
                                border-left:4px solid var(--accent-color);">
                        <h4 style="color:var(--accent-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-network-wired"></i> Configuration réseau
                        </h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP NMS</label>
                                <input x-model="formData.ip_nms" type="text" placeholder="ex. 10.0.1.10"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:monospace;
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--accent-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">VLAN NMS</label>
                                <input x-model="formData.vlan_nms" type="number" placeholder="ex. 100"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:monospace;
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--accent-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP Service</label>
                                <input x-model="formData.ip_service" type="text" placeholder="ex. 192.168.10.1"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:monospace;
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--accent-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">VLAN Service</label>
                                <input x-model="formData.vlan_service" type="number" placeholder="ex. 200"
                                       style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                              border-radius:var(--border-radius);font-family:monospace;
                                              font-size:.95rem;transition:var(--transition);"
                                       onfocus="this.style.borderColor='var(--accent-color)'"
                                       onblur="this.style.borderColor='var(--border-color)'">
                            </div>

                            {{-- IP Management — routeur uniquement --}}
                            <template x-if="modalData.type === 'router'">
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">IP Management</label>
                                    <input x-model="formData.management_ip" type="text" placeholder="ex. 172.16.0.1"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;
                                                  font-size:.95rem;transition:var(--transition);"
                                           onfocus="this.style.borderColor='var(--accent-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ④ Ports & VLANs — switch uniquement --}}
                    <template x-if="modalData.type === 'switch'">
                        <div style="background:#f0fdf4;padding:20px;
                                    border-radius:var(--border-radius);
                                    border-left:4px solid var(--success-color);">
                            <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-plug"></i> Ports &amp; VLANs
                            </h4>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ports total</label>
                                    <input x-model="formData.ports_total" type="number" min="1" placeholder="ex. 48"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ports utilisés</label>
                                    <input x-model="formData.ports_used" type="number" min="0" placeholder="ex. 32"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre de VLANs</label>
                                    <input x-model="formData.vlans" type="number" min="1" placeholder="ex. 10"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Version firmware</label>
                                    <input x-model="formData.firmware_version" type="text" placeholder="ex. 16.12.4"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ④ Interfaces — routeur uniquement --}}
                    <template x-if="modalData.type === 'router'">
                        <div style="background:#f0fdf4;padding:20px;
                                    border-radius:var(--border-radius);
                                    border-left:4px solid var(--success-color);">
                            <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-ethernet"></i> Interfaces
                            </h4>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre total d'interfaces</label>
                                    <input x-model="formData.interfaces_count" type="number" min="0" placeholder="ex. 24"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Interfaces actives (UP)</label>
                                    <input x-model="formData.interfaces_up_count" type="number" min="0" placeholder="ex. 22"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--success-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ④ Politiques & Performance — firewall uniquement --}}
                    <template x-if="modalData.type === 'firewall'">
                        <div style="background:#fef2f2;padding:20px;
                                    border-radius:var(--border-radius);
                                    border-left:4px solid var(--danger-color);">
                            <h4 style="color:var(--danger-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                                <i class="fas fa-shield-alt"></i> Politiques de sécurité &amp; Performance
                            </h4>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre de règles</label>
                                    <input x-model="formData.security_policies_count" type="number" min="0" placeholder="ex. 150"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--danger-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">CPU (%)</label>
                                    <input x-model="formData.cpu" type="number" min="0" max="100" placeholder="ex. 42"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--danger-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                                <div>
                                    <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Mémoire (%)</label>
                                    <input x-model="formData.memory" type="number" min="0" max="100" placeholder="ex. 67"
                                           style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                                  border-radius:var(--border-radius);font-family:monospace;"
                                           onfocus="this.style.borderColor='var(--danger-color)'"
                                           onblur="this.style.borderColor='var(--border-color)'">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ⑤ Configuration (commun à tous) --}}
                    <div style="background:#f8fafc;padding:20px;
                                border-radius:var(--border-radius);
                                border-left:4px solid var(--info-color);">
                        <h4 style="color:var(--info-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-code"></i> Configuration
                            <span style="font-weight:400;font-size:.85rem;color:var(--text-light);">(optionnel)</span>
                        </h4>
                        <textarea x-model="formData.configuration" rows="5"
                                  :placeholder="modalData.type==='firewall'
                                      ? 'Collez ici la configuration initiale (FortiOS, PAN-OS, etc.)…'
                                      : 'Collez ici la configuration initiale (Cisco IOS, etc.)…'"
                                  style="width:100%;padding:12px 14px;border:2px solid var(--border-color);
                                         border-radius:var(--border-radius);font-family:monospace;
                                         font-size:.88rem;line-height:1.6;resize:vertical;
                                         transition:var(--transition);color:var(--text-color);"
                                  onfocus="this.style.borderColor='var(--info-color)'"
                                  onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>
                </div>
            </template>

        </div>{{-- /body --}}

        {{-- ── FOOTER (commun) ────────────────────────────────────────── --}}
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
                    : (modalData.type === 'site'
                        ? 'Créer le site'
                        : (modalData.type === 'user'
                            ? 'Créer l\'utilisateur'
                            : 'Créer le ' + (modalData.type === 'switch' ? 'switch'
                                           : modalData.type === 'router' ? 'routeur'
                                           : 'firewall')))">
                </span>
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 2 : DÉTAILS D'ÉQUIPEMENT
     ID : equipmentDetailsModal
     Condition : currentModal === 'view'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="equipmentDetailsModal"
     x-show="currentModal === 'view'"
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
                   :class="modalData.type==='switch'   ? 'fa-exchange-alt'
                          :modalData.type==='router'   ? 'fa-route'
                          :modalData.type==='firewall' ? 'fa-fire'
                          : (modalData.type==='site' ? 'fa-building' : 'fa-server')"></i>
                <span x-text="modalData.item?.name || 'Détails'"></span>
            </h3>
            <button @click="closeModal('equipmentDetailsModal')"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;
                           font-size:1.5rem;width:40px;height:40px;border-radius:50%;
                           cursor:pointer;transition:var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── BODY ──────────────────────────────────────────────────── --}}
        <div style="padding:24px;" x-html="renderEquipmentDetails()"></div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:space-between;align-items:center;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">

            {{-- Boutons contextuels (gauche) --}}
            <div style="display:flex;gap:10px;">
                <button class="btn btn-outline btn-sm"
                        @click="testConnectivity(modalData.type, modalData.item?.id);
                                closeModal('equipmentDetailsModal')">
                    <i class="fas fa-plug"></i> Tester
                </button>

                <template x-if="modalData.type === 'switch'">
                    <button class="btn btn-outline btn-sm"
                            @click="configurePorts(modalData.item?.id);
                                    closeModal('equipmentDetailsModal')">
                        <i class="fas fa-cog"></i> Configurer ports
                    </button>
                </template>
                <template x-if="modalData.type === 'router'">
                    <button class="btn btn-outline btn-sm"
                            @click="updateInterfaces(modalData.item?.id);
                                    closeModal('equipmentDetailsModal')">
                        <i class="fas fa-ethernet"></i> Interfaces
                    </button>
                </template>
                <template x-if="modalData.type === 'firewall'">
                    <button class="btn btn-outline btn-sm"
                            @click="updateSecurityPolicies(modalData.item?.id);
                                    closeModal('equipmentDetailsModal')">
                        <i class="fas fa-shield-alt"></i> Politiques
                    </button>
                </template>
            </div>

            {{-- Fermer + Modifier (droite) --}}
            <div style="display:flex;gap:12px;">
                <button class="btn btn-outline" @click="closeModal('equipmentDetailsModal')">
                    <i class="fas fa-times"></i> Fermer
                </button>
                {{-- Bouton Modifier --}}
                <button class="btn btn-primary"
                        x-show="permissions.create"
                        @click="editEquipment(modalData.type, modalData.item?.id);
                                closeModal('equipmentDetailsModal')">
                    <i class="fas fa-edit"></i> Modifier
                </button>
            </div>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 3 : TEST DE CONNECTIVITÉ
     ID : testConnectivityModal
     Condition : currentModal === 'test'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="testConnectivityModal"
     x-show="currentModal === 'test'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:640px;max-height:90vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER — couleur dynamique ─────────────────────────────── --}}
        <div :style="{
                 background: modalData.type === 'switch'
                     ? 'linear-gradient(135deg,#0ea5e9,#0284c7)'
                     : modalData.type === 'router'
                         ? 'linear-gradient(135deg,#10b981,#059669)'
                         : 'linear-gradient(135deg,#ef4444,#dc2626)'
             }"
             style="padding:24px;border-bottom:2px solid rgba(255,255,255,0.15);
                    display:flex;justify-content:space-between;align-items:center;
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;">
                <i class="fas"
                   :class="modalData.type==='switch'   ? 'fa-exchange-alt'
                          :modalData.type==='router'   ? 'fa-route'
                          :modalData.type==='firewall' ? 'fa-fire'
                          :'fa-plug'"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('testConnectivityModal')"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;
                           font-size:1.5rem;width:40px;height:40px;border-radius:50%;
                           cursor:pointer;transition:var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── BODY ──────────────────────────────────────────────────── --}}
        <div style="padding:24px;display:grid;gap:16px;">

            {{-- Résumé équipement --}}
            <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);
                        border-left:4px solid var(--primary-color);text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;"
                     :style="{ color: modalData.type==='switch'   ? 'var(--primary-color)'
                                     : modalData.type==='router'  ? 'var(--success-color)'
                                     : 'var(--danger-color)' }">
                    <i class="fas"
                       :class="modalData.type==='switch'   ? 'fa-exchange-alt'
                              :modalData.type==='router'   ? 'fa-route'
                              :modalData.type==='firewall' ? 'fa-fire'
                              :'fa-server'"></i>
                </div>
                <div style="font-weight:700;font-size:1.15rem;" x-text="modalData.item?.name || 'Équipement'"></div>
                <div style="color:var(--text-light);font-size:.85rem;margin-top:4px;">
                    <span x-text="modalData.item?.model || ''"></span>
                    <span x-show="modalData.item?.site"> · <span x-text="modalData.item?.site"></span></span>
                </div>
            </div>

            {{-- Résultats des tests --}}
            <div x-html="renderTestResults()"></div>

        </div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('testConnectivityModal')">
                <i class="fas fa-times"></i> Fermer
            </button>
            <button class="btn btn-primary"
                    @click="testConnectivity(modalData.type, modalData.item?.id)">
                <i class="fas fa-redo"></i> Relancer le test
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 4 : CONFIGURATION DES PORTS — SWITCH uniquement
     Condition : currentModal === 'configurePorts'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="configurePortsModal"
     x-show="currentModal === 'configurePorts'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:760px;max-height:90vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid rgba(255,255,255,0.15);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,var(--success-color) 0%,#059669 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;">
                <i class="fas fa-cog"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('configurePortsModal')"
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

            {{-- Infos switch --}}
            <div style="background:#f0fdf4;padding:16px;border-radius:var(--border-radius);
                        border-left:4px solid var(--success-color);
                        display:flex;align-items:center;gap:16px;">
                <div style="font-size:2rem;color:var(--success-color);">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1.1rem;" x-text="modalData.item?.name || 'Switch'"></div>
                    <div style="color:var(--text-light);font-size:.85rem;margin-top:4px;">
                        <span x-text="modalData.item?.ports_used || 0"></span> /
                        <span x-text="modalData.item?.ports_total || 0"></span> ports utilisés
                        &nbsp;·&nbsp;
                        <span x-text="modalData.item?.site || 'N/A'"></span>
                    </div>
                </div>
            </div>

            {{-- Upload de fichier JSON --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--success-color);">
                <h4 style="color:var(--success-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-upload"></i> Charger un fichier de configuration
                </h4>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <input type="file"
                           accept=".json,application/json"
                           id="portConfigFile"
                           style="flex:1; padding:8px; border:2px solid var(--border-color); border-radius:var(--border-radius);">
                    <button class="btn btn-primary"
                            style="background:linear-gradient(135deg,var(--success-color),#059669);"
                            @click="uploadPortConfig()">
                        <i class="fas fa-file-upload"></i> Charger
                    </button>
                </div>
                <p style="margin:8px 0 0;font-size:.8rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i> Sélectionnez un fichier JSON conforme au format attendu.
                </p>
            </div>

            {{-- Affichage de la configuration actuelle (lecture seule) --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--success-color);">
                <h4 style="color:var(--success-color);margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code"></i> Configuration actuelle
                    <span style="font-weight:400;font-size:.8rem;color:var(--text-light);">(JSON — lecture seule)</span>
                </h4>
                <pre style="background:white; padding:12px; border:2px solid var(--border-color);
                           border-radius:var(--border-radius); font-family:monospace;
                           font-size:.85rem; line-height:1.6; overflow-x:auto; white-space:pre-wrap;
                           max-height:300px; color:var(--text-color);"
                     x-text="formData.portConfiguration || 'Aucune configuration chargée'">
                </pre>
            </div>

        </div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('configurePortsModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary"
                    style="background:linear-gradient(135deg,var(--success-color),#059669);"
                    @click="savePortConfiguration()">
                <i class="fas fa-save"></i> Appliquer la configuration
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 5 : MISE À JOUR DES INTERFACES — ROUTEUR uniquement
     Condition : currentModal === 'updateInterfaces'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="updateInterfacesModal"
     x-show="currentModal === 'updateInterfaces'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:760px;max-height:90vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid rgba(255,255,255,0.15);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,#10b981 0%,#059669 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;">
                <i class="fas fa-ethernet"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('updateInterfacesModal')"
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

            {{-- Infos routeur --}}
            <div style="background:#f0fdf4;padding:16px;border-radius:var(--border-radius);
                        border-left:4px solid #10b981;
                        display:flex;align-items:center;gap:16px;">
                <div style="font-size:2rem;color:#10b981;">
                    <i class="fas fa-route"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1.1rem;" x-text="modalData.item?.name || 'Routeur'"></div>
                    <div style="color:var(--text-light);font-size:.85rem;display:flex;gap:16px;margin-top:4px;">
                        <span>
                            <i class="fas fa-ethernet"></i>
                            <span x-text="modalData.item?.interfaces_up_count || 0"></span> /
                            <span x-text="modalData.item?.interfaces_count || 0"></span> interfaces actives
                        </span>
                        <span x-show="modalData.item?.site">
                            <i class="fas fa-building"></i>
                            <span x-text="modalData.item?.site"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Configuration JSON --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid #10b981;">
                <h4 style="color:#10b981;margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code"></i> Configuration des interfaces
                    <span style="font-weight:400;font-size:.8rem;color:var(--text-light);">(JSON — interface, status, ip, description)</span>
                </h4>
                <textarea x-model="formData.interfacesConfig"
                          rows="12"
                          placeholder='[
  {
    "interface": "GigabitEthernet0/0",
    "status": "up",
    "ip": "192.168.1.1",
    "mask": "255.255.255.0",
    "description": "LAN Principal"
  },
  {
    "interface": "GigabitEthernet0/1",
    "status": "down",
    "ip": "",
    "mask": "",
    "description": "Lien WAN (désactivé)"
  }
]'
                          style="width:100%;padding:12px 14px;border:2px solid var(--border-color);
                                 border-radius:var(--border-radius);font-family:monospace;
                                 font-size:.85rem;line-height:1.7;resize:vertical;
                                 transition:var(--transition);color:var(--text-color);"
                          onfocus="this.style.borderColor='#10b981'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                <p style="margin:8px 0 0;font-size:.8rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i>
                    Valeurs acceptées pour <code>status</code> : <code>up</code> | <code>down</code>.
                </p>
            </div>

        </div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('updateInterfacesModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary"
                    style="background:linear-gradient(135deg,#10b981,#059669);"
                    @click="saveInterfacesUpdate()">
                <i class="fas fa-save"></i> Appliquer la configuration
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 6 : POLITIQUES DE SÉCURITÉ — FIREWALL uniquement
     Condition : currentModal === 'updateSecurityPolicies'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="updateSecurityPoliciesModal"
     x-show="currentModal === 'updateSecurityPolicies'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:760px;max-height:90vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid rgba(255,255,255,0.15);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,var(--danger-color) 0%,#dc2626 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;">
                <i class="fas fa-shield-alt"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('updateSecurityPoliciesModal')"
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

            {{-- Infos firewall --}}
            <div style="background:#fef2f2;padding:16px;border-radius:var(--border-radius);
                        border-left:4px solid var(--danger-color);
                        display:flex;align-items:center;gap:16px;">
                <div style="font-size:2rem;color:var(--danger-color);">
                    <i class="fas fa-fire"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1.1rem;" x-text="modalData.item?.name || 'Firewall'"></div>
                    <div style="color:var(--text-light);font-size:.85rem;display:flex;gap:16px;margin-top:4px;">
                        <span>
                            <i class="fas fa-list-ul"></i>
                            <span x-text="modalData.item?.security_policies_count || 0"></span> règles actives
                        </span>
                        <span x-show="modalData.item?.site">
                            <i class="fas fa-building"></i>
                            <span x-text="modalData.item?.site"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Upload de fichier JSON --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--danger-color);">
                <h4 style="color:var(--danger-color);margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-upload"></i> Charger un fichier de politiques
                </h4>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <input type="file"
                           accept=".json,application/json"
                           id="securityPoliciesFile"
                           style="flex:1; padding:8px; border:2px solid var(--border-color); border-radius:var(--border-radius);">
                    <button class="btn btn-primary"
                            style="background:linear-gradient(135deg,var(--danger-color),#dc2626);"
                            @click="uploadSecurityPolicies()">
                        <i class="fas fa-file-upload"></i> Charger
                    </button>
                </div>
                <p style="margin:8px 0 0;font-size:.8rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i> Sélectionnez un fichier JSON conforme au format attendu.
                </p>
            </div>

            {{-- Affichage des politiques actuelles (lecture seule) --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--danger-color);">
                <h4 style="color:var(--danger-color);margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code"></i> Politiques actuelles
                    <span style="font-weight:400;font-size:.8rem;color:var(--text-light);">(JSON — lecture seule)</span>
                </h4>
                <pre style="background:white; padding:12px; border:2px solid var(--border-color);
                           border-radius:var(--border-radius); font-family:monospace;
                           font-size:.85rem; line-height:1.6; overflow-x:auto; white-space:pre-wrap;
                           max-height:300px; color:var(--text-color);"
                     x-text="formData.securityPolicies || 'Aucune politique chargée'">
                </pre>
            </div>

        </div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('updateSecurityPoliciesModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary"
                    style="background:linear-gradient(135deg,var(--danger-color),#dc2626);"
                    @click="saveSecurityPolicies()">
                <i class="fas fa-save"></i> Appliquer les politiques
            </button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 7 : CONFIRMATION CHANGEMENT DE STATUT UTILISATEUR
     Condition : currentModal === 'toggleUserStatus'
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="toggleUserStatusModal"
     x-show="currentModal === 'toggleUserStatus'"
     x-cloak
     style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);z-index:1000;
            display:flex;align-items:center;justify-content:center;">

    <div style="background:white;border-radius:var(--border-radius-lg);
                width:92%;max-width:500px;max-height:90vh;overflow-y:auto;
                box-shadow:var(--card-shadow-hover);animation:fadeIn .3s ease;">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div style="padding:24px;border-bottom:2px solid var(--border-color);
                    display:flex;justify-content:space-between;align-items:center;
                    background:linear-gradient(135deg,var(--warning-color) 0%,#d97706 100%);
                    color:white;
                    border-radius:var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin:0;font-size:1.4rem;display:flex;align-items:center;gap:12px;">
                <i class="fas fa-toggle-on"></i>
                <span>Confirmation</span>
            </h3>
            <button @click="closeModal('toggleUserStatusModal')"
                    style="background:rgba(255,255,255,0.2);border:none;color:white;
                           font-size:1.5rem;width:40px;height:40px;border-radius:50%;
                           cursor:pointer;transition:var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── BODY ──────────────────────────────────────────────────── --}}
        <div style="padding:24px;text-align:center;">
            <div style="font-size:3rem;color:var(--warning-color);margin-bottom:16px;">
                <i class="fas" :class="userToToggle?.is_active ? 'fa-toggle-off' : 'fa-toggle-on'"></i>
            </div>
            <p style="font-size:1.1rem;margin-bottom:8px;">
                Êtes-vous sûr de vouloir
                <strong x-text="userToToggle?.is_active ? 'désactiver' : 'activer'"></strong>
                l'utilisateur
            </p>
            <p style="font-size:1.3rem;font-weight:700;color:var(--primary-color);margin-bottom:16px;"
               x-text="userToToggle?.name"></p>
            <p style="color:var(--text-light);font-size:.9rem;">
                Cette action modifiera ses permissions d'accès à la plateforme.
            </p>
        </div>

        {{-- ── FOOTER ────────────────────────────────────────────────── --}}
        <div style="padding:20px 24px;border-top:2px solid var(--border-color);
                    display:flex;justify-content:flex-end;gap:12px;
                    background:#f8fafc;
                    border-radius:0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('toggleUserStatusModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary"
                    style="background:linear-gradient(135deg,var(--warning-color),#d97706);"
                    @click="confirmToggleUserStatus()">
                <i class="fas fa-check"></i> Confirmer
            </button>
        </div>

    </div>
</div>