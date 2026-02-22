{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  dashboard/partials/modals.blade.php                                ║
    ║  SOURCE UNIQUE de vérité pour TOUS les modaux de l'application.     ║
    ║  Inclus une seule fois depuis la vue principale (dashboard.blade).  ║
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
        - createEquipmentModal       (create / edit — switch | router | firewall)
        - equipmentDetailsModal      (détails — tous types)
        - testConnectivityModal      (test — tous types, couleur dynamique)
        - configurePortsModal        (switch uniquement)
        - updateInterfacesModal      (router uniquement)
        - updateSecurityPoliciesModal(firewall uniquement)
--}}


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 1 : CRÉATION / ÉDITION
     Condition : currentModal === 'create'
     Type discriminant : modalData.type = 'switch' | 'router' | 'firewall'
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
                {{-- Icône contextuelle selon le type --}}
                <i class="fas"
                   :class="modalData.type==='switch'   ? 'fa-exchange-alt'
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
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--success-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Ports utilisés</label>
                            <input x-model="formData.ports_used" type="number" min="0" placeholder="ex. 32"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--success-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Nombre de VLANs</label>
                            <input x-model="formData.vlans" type="number" min="1" placeholder="ex. 10"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--success-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Version firmware</label>
                            <input x-model="formData.firmware_version" type="text" placeholder="ex. 16.12.4"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
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
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--success-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Interfaces actives (UP)</label>
                            <input x-model="formData.interfaces_up_count" type="number" min="0" placeholder="ex. 22"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
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
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--danger-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">CPU (%)</label>
                            <input x-model="formData.cpu" type="number" min="0" max="100" placeholder="ex. 42"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
                                   onfocus="this.style.borderColor='var(--danger-color)'"
                                   onblur="this.style.borderColor='var(--border-color)'">
                        </div>

                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);display:block;margin-bottom:6px;font-weight:600;">Mémoire (%)</label>
                            <input x-model="formData.memory" type="number" min="0" max="100" placeholder="ex. 67"
                                   style="width:100%;padding:10px 14px;border:2px solid var(--border-color);
                                          border-radius:var(--border-radius);font-family:monospace;
                                          font-size:.95rem;transition:var(--transition);"
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
                    : 'Créer le ' + (modalData.type === 'switch' ? 'switch'
                                   : modalData.type === 'router' ? 'routeur'
                                   : 'firewall')">
                </span>
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODAL 2 : DÉTAILS D'ÉQUIPEMENT
     ID unique : equipmentDetailsModal  (anciens viewEquipmentModal supprimés)
     Condition : currentModal === 'view'
     Footer : Fermer + Modifier (conditionnel) + bouton contextuel selon type
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
                          :'fa-server'"></i>
                <span x-text="modalData.item?.name || 'Détails de l\'équipement'"></span>
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

                {{-- Switch → Configurer ports --}}
                <template x-if="modalData.type === 'switch'">
                    <button class="btn btn-outline btn-sm"
                            @click="configurePorts(modalData.item?.id);
                                    closeModal('equipmentDetailsModal')">
                        <i class="fas fa-cog"></i> Configurer ports
                    </button>
                </template>

                {{-- Router → Interfaces --}}
                <template x-if="modalData.type === 'router'">
                    <button class="btn btn-outline btn-sm"
                            @click="updateInterfaces(modalData.item?.id);
                                    closeModal('equipmentDetailsModal')">
                        <i class="fas fa-ethernet"></i> Interfaces
                    </button>
                </template>

                {{-- Firewall → Politiques --}}
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
                {{-- ✅ Bouton Modifier aligné avec le modal de référence --}}
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
     ID unique : testConnectivityModal  (switchTestConnectivityModal supprimé)
     Condition : currentModal === 'test'
     Header : couleur dynamique selon modalData.type
     Bouton relancer : générique modalData.type + modalData.item?.id
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
            {{-- ✅ Générique : type + id depuis modalData --}}
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

            {{-- Configuration JSON --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--success-color);">
                <h4 style="color:var(--success-color);margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code"></i> Configuration des ports
                    <span style="font-weight:400;font-size:.8rem;color:var(--text-light);">(JSON — port, status, vlan, description)</span>
                </h4>
                <textarea x-model="formData.portConfiguration"
                          rows="10"
                          placeholder='[
  { "port": 1, "status": "enabled", "vlan": 100, "description": "Serveur Web" },
  { "port": 2, "status": "disabled", "vlan": 200, "description": "Réservé" }
]'
                          style="width:100%;padding:12px 14px;border:2px solid var(--border-color);
                                 border-radius:var(--border-radius);font-family:monospace;
                                 font-size:.85rem;line-height:1.7;resize:vertical;
                                 transition:var(--transition);color:var(--text-color);"
                          onfocus="this.style.borderColor='var(--success-color)'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                <p style="margin:8px 0 0;font-size:.8rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i>
                    Valeurs acceptées pour <code>status</code> : <code>enabled</code> | <code>disabled</code>.
                    <code>vlan</code> entre 1 et 4094.
                </p>
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

            {{-- Configuration JSON --}}
            <div style="background:#f8fafc;padding:20px;
                        border-radius:var(--border-radius);
                        border-left:4px solid var(--danger-color);">
                <h4 style="color:var(--danger-color);margin:0 0 12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code"></i> Politiques de sécurité
                    <span style="font-weight:400;font-size:.8rem;color:var(--text-light);">(JSON — name, src, dst, action, port)</span>
                </h4>
                <textarea x-model="formData.securityPolicies"
                          rows="12"
                          placeholder='[
  {
    "name": "Allow-HTTP",
    "source": "192.168.1.0/24",
    "destination": "0.0.0.0/0",
    "port": 80,
    "protocol": "TCP",
    "action": "allow"
  },
  {
    "name": "Block-Telnet",
    "source": "0.0.0.0/0",
    "destination": "0.0.0.0/0",
    "port": 23,
    "protocol": "TCP",
    "action": "deny"
  }
]'
                          style="width:100%;padding:12px 14px;border:2px solid var(--border-color);
                                 border-radius:var(--border-radius);font-family:monospace;
                                 font-size:.85rem;line-height:1.7;resize:vertical;
                                 transition:var(--transition);color:var(--text-color);"
                          onfocus="this.style.borderColor='var(--danger-color)'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                <p style="margin:8px 0 0;font-size:.8rem;color:var(--text-light);">
                    <i class="fas fa-info-circle"></i>
                    Valeurs acceptées pour <code>action</code> : <code>allow</code> | <code>deny</code> | <code>drop</code>.
                </p>
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