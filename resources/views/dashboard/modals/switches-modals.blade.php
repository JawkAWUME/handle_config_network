{{-- ══════════════════════════════════════════════════════════════
     MODAL 1 : CRÉATION / ÉDITION — SWITCH
     ══════════════════════════════════════════════════════════════ --}}
<div id="createEquipmentModal"
     x-show="currentModal === 'create' && modalData.type === 'switch'"
     x-cloak
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            display: flex; align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 860px; max-height: 92vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- HEADER --}}
        <div style="padding: 24px; border-bottom: 2px solid var(--border-color);
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-exchange-alt"></i>
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

            {{-- 1. Informations générales --}}
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
                        <input x-model="formData.name" type="text" placeholder="ex. SW-PARIS-01"
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
                        <input x-model="formData.brand" type="text" placeholder="ex. Cisco, HP, Dell…"
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
                        <input x-model="formData.model" type="text" placeholder="ex. Catalyst 9300"
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
                        <input x-model="formData.serial_number" type="text" placeholder="ex. FDO2049Z0CL"
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
                                <input type="radio" x-model="formData.status" value="active"
                                       style="accent-color:var(--success-color); width:16px; height:16px;">
                                <span style="color:var(--success-color);"><i class="fas fa-check-circle"></i> Actif</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
                                <input type="radio" x-model="formData.status" value="danger"
                                       style="accent-color:var(--danger-color); width:16px; height:16px;">
                                <span style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> Inactif</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 2. Credentials d'accès --}}
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

            {{-- 3. Configuration réseau --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--accent-color);">
                <h4 style="color:var(--accent-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-network-wired"></i> Configuration réseau
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">IP NMS</label>
                        <input x-model="formData.ip_nms" type="text" placeholder="ex. 10.0.1.10"
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
                        <input x-model="formData.ip_service" type="text" placeholder="ex. 192.168.10.1"
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

            {{-- 4. Ports & VLANs --}}
            <div style="background: #f0fdf4; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--success-color);">
                <h4 style="color:var(--success-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-plug"></i> Ports &amp; VLANs
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Ports total
                        </label>
                        <input x-model="formData.ports_total" type="number" min="1" placeholder="ex. 48"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Ports utilisés
                        </label>
                        <input x-model="formData.ports_used" type="number" min="0" placeholder="ex. 32"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Nombre de VLANs
                        </label>
                        <input x-model="formData.vlans" type="number" min="1" placeholder="ex. 10"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                    <div>
                        <label style="font-size:.85rem; color:var(--text-light); display:block; margin-bottom:6px; font-weight:600;">
                            Version firmware
                        </label>
                        <input x-model="formData.firmware_version" type="text" placeholder="ex. 16.12.4"
                               style="width:100%; padding:10px 14px; border:2px solid var(--border-color);
                                      border-radius:var(--border-radius); font-family:monospace;
                                      font-size:.95rem; transition:var(--transition);"
                               onfocus="this.style.borderColor='var(--success-color)'"
                               onblur="this.style.borderColor='var(--border-color)'">
                    </div>

                </div>
            </div>

            {{-- 5. Configuration (optionnel) --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--info-color);">
                <h4 style="color:var(--info-color); margin: 0 0 16px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-code"></i> Configuration
                    <span style="font-weight:400; font-size:.85rem; color:var(--text-light);">(optionnel)</span>
                </h4>
                <textarea x-model="formData.configuration"
                          rows="5"
                          placeholder="Collez ici la configuration initiale (Cisco IOS, etc.)…"
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
                <span x-text="modalData.id ? 'Enregistrer les modifications' : 'Créer le switch'"></span>
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL 2 : TEST DE CONNECTIVITÉ — SWITCH
     ══════════════════════════════════════════════════════════════ --}}
<div id="switchTestConnectivityModal"
     x-show="currentModal === 'test' && modalData.type === 'switch'"
     x-cloak
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            display: flex; align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 640px; max-height: 90vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- HEADER --}}
        <div style="padding: 24px;
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--info-color) 0%, #1d4ed8 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.4rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-plug"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('testConnectivityModal')"
                    style="background: rgba(255,255,255,0.2); border: none; color: white;
                           font-size: 1.5rem; width: 40px; height: 40px; border-radius: 50%;
                           cursor: pointer; transition: var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div style="padding: 24px; display: grid; gap: 16px;">

            {{-- Résumé équipement --}}
            <div style="background: #eff6ff; padding: 16px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--info-color); text-align: center;">
                <div style="font-size: 2.5rem; color: var(--info-color); margin-bottom: 8px;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h4 style="color: var(--info-color); margin: 0 0 4px;" x-text="modalData.item?.name || 'Switch'"></h4>
                <small style="color: var(--text-light);" x-text="modalData.item?.model || ''"></small>
            </div>

            {{-- Résultats des tests --}}
            <div style="display: grid; gap: 10px;" x-html="renderTestResults()"></div>

        </div>

        {{-- FOOTER --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: flex-end; gap: 12px;
                    background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('testConnectivityModal')">
                <i class="fas fa-times"></i> Fermer
            </button>
            <button class="btn btn-primary" @click="testConnectivity('switch', modalData.item?.id)">
                <i class="fas fa-redo"></i> Relancer le test
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL 3 : CONFIGURATION DES PORTS — SWITCH
     ══════════════════════════════════════════════════════════════ --}}
<div id="configurePortsModal"
     x-show="currentModal === 'configurePorts'"
     x-cloak
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            display: flex; align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 760px; max-height: 90vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- HEADER --}}
        <div style="padding: 24px;
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.4rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-cog"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('configurePortsModal')"
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

            {{-- Infos switch --}}
            <div style="background: #f0fdf4; padding: 16px; border-radius: var(--border-radius);
                        border-left: 4px solid var(--success-color);
                        display: flex; align-items: center; gap: 16px;">
                <div style="font-size: 2rem; color: var(--success-color);">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 1.1rem;" x-text="modalData.item?.name || 'Switch'"></div>
                    <div style="color: var(--text-light); font-size: .85rem;">
                        <span x-text="modalData.item?.ports_used || 0"></span> /
                        <span x-text="modalData.item?.ports_total || 0"></span> ports utilisés
                        &nbsp;·&nbsp;
                        <span x-text="modalData.item?.site || 'N/A'"></span>
                    </div>
                </div>
            </div>

            {{-- Configuration JSON --}}
            <div style="background: #f8fafc; padding: 20px;
                        border-radius: var(--border-radius);
                        border-left: 4px solid var(--success-color);">
                <h4 style="color:var(--success-color); margin: 0 0 12px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-code"></i> Configuration des ports
                    <span style="font-weight:400; font-size:.8rem; color:var(--text-light);">
                        (JSON — port, status, vlan, description)
                    </span>
                </h4>
                <textarea x-model="formData.portConfiguration"
                          rows="10"
                          placeholder='[
  { "port": 1, "status": "enabled", "vlan": 100, "description": "Serveur Web" },
  { "port": 2, "status": "disabled", "vlan": 200, "description": "Réservé" }
]'
                          style="width:100%; padding:12px 14px; border:2px solid var(--border-color);
                                 border-radius:var(--border-radius); font-family:monospace;
                                 font-size:.85rem; line-height:1.7; resize:vertical;
                                 transition:var(--transition); color:var(--text-color);"
                          onfocus="this.style.borderColor='var(--success-color)'"
                          onblur="this.style.borderColor='var(--border-color)'"></textarea>
                <p style="margin: 8px 0 0; font-size:.8rem; color:var(--text-light);">
                    <i class="fas fa-info-circle"></i>
                    Valeurs acceptées pour <code>status</code> : <code>enabled</code> | <code>disabled</code>.
                    <code>vlan</code> entre 1 et 4094.
                </p>
            </div>

        </div>

        {{-- FOOTER --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: flex-end; gap: 12px;
                    background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <button class="btn btn-outline" @click="closeModal('configurePortsModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button class="btn btn-primary" style="background: linear-gradient(135deg, var(--success-color), #059669);"
                    @click="savePortConfiguration()">
                <i class="fas fa-save"></i> Appliquer la configuration
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL 4 : DÉTAILS — SWITCH
     ══════════════════════════════════════════════════════════════ --}}
<div id="viewEquipmentModal"
     x-show="currentModal === 'view' && modalData.type === 'switch'"
     x-cloak
     style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 1000;
            display: flex; align-items: center; justify-content: center;">

    <div style="background: white; border-radius: var(--border-radius-lg);
                width: 92%; max-width: 860px; max-height: 92vh; overflow-y: auto;
                box-shadow: var(--card-shadow-hover); animation: fadeIn .3s ease;">

        {{-- HEADER --}}
        <div style="padding: 24px;
                    display: flex; justify-content: space-between; align-items: center;
                    background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-light) 100%);
                    color: white;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
            <h3 style="margin: 0; font-size: 1.4rem; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-eye"></i>
                <span x-text="modalTitle"></span>
            </h3>
            <button @click="closeModal('viewEquipmentModal')"
                    style="background: rgba(255,255,255,0.2); border: none; color: white;
                           font-size: 1.5rem; width: 40px; height: 40px; border-radius: 50%;
                           cursor: pointer; transition: var(--transition);"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div style="padding: 24px;" x-html="renderEquipmentDetails()"></div>

        {{-- FOOTER --}}
        <div style="padding: 20px 24px; border-top: 2px solid var(--border-color);
                    display: flex; justify-content: space-between; align-items: center;
                    background: #f8fafc;
                    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
            <div style="display:flex; gap:10px;">
                <button class="btn btn-outline btn-sm"
                        @click="testConnectivity('switch', modalData.item?.id); closeModal('viewEquipmentModal')">
                    <i class="fas fa-plug"></i> Tester
                </button>
                <button class="btn btn-outline btn-sm"
                        @click="configurePorts(modalData.item?.id); closeModal('viewEquipmentModal')">
                    <i class="fas fa-cog"></i> Configurer ports
                </button>
            </div>
            <button class="btn btn-outline" @click="closeModal('viewEquipmentModal')">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>

    </div>
</div>