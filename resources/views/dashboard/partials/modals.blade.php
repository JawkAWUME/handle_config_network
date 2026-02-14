{{-- Modal de création/édition (switch, router, firewall) --}}
<div id="createEquipmentModal" 
     class="modal-overlay" 
     x-show="currentModal === 'create'"
     @click.self="closeModal('createEquipmentModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('createEquipmentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="equipmentForm" @submit.prevent="saveEquipment">
                <template x-if="modalData.type === 'switch'">
                    @include('dashboard.modals.form-switch')
                </template>
                <template x-if="modalData.type === 'router'">
                    @include('dashboard.modals.form-router')
                </template>
                <template x-if="modalData.type === 'firewall'">
                    @include('dashboard.modals.form-firewall')
                </template>
                {{-- Site non inclus (pas d'API) --}}
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" @click="closeModal('createEquipmentModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button type="submit" form="equipmentForm" class="btn btn-primary">
                <i class="fas fa-save"></i> <span x-text="modalData.id ? 'Mettre à jour' : 'Enregistrer'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Modal de visualisation --}}
<div id="viewEquipmentModal" 
     class="modal-overlay" 
     x-show="currentModal === 'view'"
     @click.self="closeModal('viewEquipmentModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('viewEquipmentModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="equipmentDetails" x-html="renderDetails()" class="equipment-details"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" @click="closeModal('viewEquipmentModal')">Fermer</button>
        </div>
    </div>
</div>

{{-- Modal de test de connectivité --}}
<div id="testConnectivityModal" 
     class="modal-overlay" 
     x-show="currentModal === 'test'"
     @click.self="closeModal('testConnectivityModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('testConnectivityModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div x-html="renderTestResults()" class="connectivity-results"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" @click="closeModal('testConnectivityModal')">Fermer</button>
        </div>
    </div>
</div>

{{-- Modal de configuration des ports (Switch) --}}
<div id="configurePortsModal" 
     class="modal-overlay" 
     x-show="currentModal === 'configurePorts'"
     @click.self="closeModal('configurePortsModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('configurePortsModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form @submit.prevent="savePortConfiguration">
                <div class="form-field">
                    <label><i class="fas fa-network-wired"></i> Configuration des ports</label>
                    <textarea x-model="formData.portConfiguration" 
                              rows="10" 
                              class="w-full px-4 py-2 border rounded-xl font-mono"
                              placeholder="Ex: port 1: VLAN 10, port 2: VLAN 20..."></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline" @click="closeModal('configurePortsModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de mise à jour des interfaces (Routeur) --}}
<div id="updateInterfacesModal" 
     class="modal-overlay" 
     x-show="currentModal === 'updateInterfaces'"
     @click.self="closeModal('updateInterfacesModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('updateInterfacesModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form @submit.prevent="saveInterfacesUpdate">
                <div class="form-field">
                    <label><i class="fas fa-ethernet"></i> Configuration des interfaces</label>
                    <textarea x-model="formData.interfacesConfig" 
                              rows="10" 
                              class="w-full px-4 py-2 border rounded-xl font-mono"
                              placeholder="Ex: interface 0: up, interface 1: down..."></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline" @click="closeModal('updateInterfacesModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de mise à jour des politiques de sécurité (Firewall) --}}
<div id="updateSecurityPoliciesModal" 
     class="modal-overlay" 
     x-show="currentModal === 'updateSecurityPolicies'"
     @click.self="closeModal('updateSecurityPoliciesModal')"
     x-cloak>
    <div class="modal-content">
        <div class="modal-header">
            <h3 x-text="modalTitle"></h3>
            <button class="close-modal" @click="closeModal('updateSecurityPoliciesModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form @submit.prevent="saveSecurityPolicies">
                <div class="form-field">
                    <label><i class="fas fa-shield-alt"></i> Politiques de sécurité</label>
                    <textarea x-model="formData.securityPolicies" 
                              rows="10" 
                              class="w-full px-4 py-2 border rounded-xl font-mono"
                              placeholder="Ex: allow any to any, deny any..."></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline" @click="closeModal('updateSecurityPoliciesModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>