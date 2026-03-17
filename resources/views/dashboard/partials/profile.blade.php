{{-- resources/views/dashboard/partials/profile.blade.php --}}
<div x-data="{ form: {
    name: '{{ $currentUser['name'] }}',
    email: '{{ $currentUser['email'] }}',
    department: '{{ $currentUser['department'] ?? '' }}',
    phone: '{{ $currentUser['phone'] ?? '' }}',
    password: '',
    password_confirmation: ''
}}">
    <form @submit.prevent="updateProfile">
        @csrf
        <div class="modal-body" style="padding: 24px;">
            {{-- Nom --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nom complet <span style="color: var(--danger-color);">*</span></label>
                <input type="text" x-model="form.name" required
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
            </div>

            {{-- Email --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Adresse e-mail <span style="color: var(--danger-color);">*</span></label>
                <input type="email" x-model="form.email" required
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
            </div>

            {{-- Département --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Département</label>
                <input type="text" x-model="form.department"
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
            </div>

            {{-- Téléphone --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Téléphone</label>
                <input type="tel" x-model="form.phone"
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
            </div>

            {{-- Mot de passe (changement facultatif) --}}
            <div style="background: #f8fafc; padding: 16px; border-radius: var(--border-radius); margin-bottom: 20px;">
                <h4 style="margin-bottom: 16px; color: var(--text-color);"><i class="fas fa-lock"></i> Changer le mot de passe</h4>
                <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 16px;">Laissez vide pour conserver le mot de passe actuel.</p>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nouveau mot de passe</label>
                    <input type="password" x-model="form.password"
                           style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Confirmer le mot de passe</label>
                    <input type="password" x-model="form.password_confirmation"
                           style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: var(--border-radius);">
                </div>
            </div>
        </div>

        <div style="padding: 20px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="btn btn-outline" @click="closeModal('profileModal')">Annuler</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
                <i class="fas fa-save" x-show="!loading"></i>
                <i class="fas fa-spinner fa-spin" x-show="loading"></i>
                Enregistrer
            </button>
        </div>
    </form>
</div>