<input type="hidden" x-model="formData.id" x-show="modalData.id">
<div class="modal-form">
    <div class="form-row">
        <div class="form-field">
            <label><i class="fas fa-tag"></i> Nom *</label>
            <input type="text" x-model="formData.name" required>
        </div>
        <div class="form-field">
            <label><i class="fas fa-building"></i> Site</label>
            <select x-model="formData.site_id">
                <option value="">Sélectionnez un site</option>
                <template x-for="site in sites" :key="site.id">
                    <option :value="site.id" x-text="site.name"></option>
                </template>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-field">
            <label><i class="fas fa-microchip"></i> Modèle</label>
            <input type="text" x-model="formData.model">
        </div>
        <div class="form-field">
            <label><i class="fas fa-industry"></i> Marque</label>
            <input type="text" x-model="formData.brand">
        </div>
    </div>
    <div class="detail-section">
        <h4><i class="fas fa-network-wired"></i> Configuration réseau</h4>
        <div class="form-row">
            <div class="form-field">
                <label>IP NMS</label>
                <input type="text" x-model="formData.ip_nms" placeholder="192.168.1.1">
            </div>
            <div class="form-field">
                <label>VLAN NMS</label>
                <input type="number" x-model="formData.vlan_nms" placeholder="10">
            </div>
        </div>
        <div class="form-row">
            <div class="form-field">
                <label>IP Service</label>
                <input type="text" x-model="formData.ip_service" placeholder="192.168.2.1">
            </div>
            <div class="form-field">
                <label>VLAN Service</label>
                <input type="number" x-model="formData.vlan_service" placeholder="20">
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-field">
            <label><i class="fas fa-plug"></i> Ports totaux</label>
            <input type="number" x-model="formData.ports" placeholder="48">
        </div>
        <div class="form-field">
            <label><i class="fas fa-sitemap"></i> VLANs configurés</label>
            <input type="number" x-model="formData.vlans" placeholder="10">
        </div>
    </div>
    <div class="form-field">
        <label><i class="fas fa-file-code"></i> Configuration (optionnel)</label>
        <textarea x-model="formData.configuration" rows="4" placeholder="Configuration initiale..."></textarea>
    </div>
</div>