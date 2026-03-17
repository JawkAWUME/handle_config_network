<div class="fade-in">
    <section class="equipment-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-users"></i> Gestion des utilisateurs
            </h2>
            <div class="section-actions">
                <span class="status-badge status-info" x-text="users.length + ' utilisateur(s)'"></span>
                <button class="btn btn-primary" @click="openCreateModal('user')">
                    <i class="fas fa-user-plus"></i> Nouvel utilisateur
                </button>
            </div>
        </div>

        {{-- KPI --}}
        <div style="display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap;">
            <div class="kpi-card" style="flex:1; min-width:140px; border-left-color:#0ea5e9;">
                <div class="kpi-value" style="color:#0ea5e9;" x-text="userTotals.total  || 0"></div>
                <div class="kpi-label">Total</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:140px; border-left-color:#10b981;">
                <div class="kpi-value" style="color:#10b981;" x-text="userTotals.active || 0"></div>
                <div class="kpi-label">Actifs</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:140px; border-left-color:#ef4444;">
                <div class="kpi-value" style="color:#ef4444;" x-text="userTotals.admins || 0"></div>
                <div class="kpi-label">Administrateurs</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:140px; border-left-color:#f59e0b;">
                <div class="kpi-value" style="color:#f59e0b;" x-text="userTotals.agents || 0"></div>
                <div class="kpi-label">Agents</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:140px; border-left-color:#3b82f6;">
                <div class="kpi-value" style="color:#3b82f6;" x-text="userTotals.viewers || 0"></div>
                <div class="kpi-label">Lecteurs</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Nom</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-shield-alt"></i> Rôle</th>
                        <th><i class="fas fa-building"></i> Département</th>
                        <th><i class="fas fa-phone"></i> Téléphone</th>
                        <th><i class="fas fa-circle"></i> Statut</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in users" :key="user.id">
                        <tr :style="!user.is_active ? 'opacity:.65;' : ''">
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                    <div class="avatar-base"
                                        :style="user.role === 'admin'
                                            ? 'background: radial-gradient(circle at 30% 30%, #ef4444, #b91c1c)'
                                            : (user.role === 'technician'
                                                ? 'background: radial-gradient(circle at 30% 30%, #f59e0b, #b45309)'
                                                : 'background: radial-gradient(circle at 30% 30%, #3b82f6, #1e40af)')">
                                        <span x-text="user.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span style="font-weight: 600; font-size: 0.95rem;" x-text="user.name"></span>
                                            <span x-show="user.is_current" class="status-badge status-info" style="font-size: 0.65rem; padding: 2px 8px;">Vous</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:.88rem;" x-text="user.email"></td>
                            <td>
                                <span class="status-badge" :class="{
                                    'status-danger':  user.role === 'admin',
                                    'status-warning': user.role === 'technician',
                                    'status-info':    user.role === 'viewer'
                                }">
                                    <i class="fas" :class="{
                                        'fa-crown':    user.role === 'admin',
                                        'fa-user-cog': user.role === 'technician',
                                        'fa-eye':      user.role === 'viewer'
                                    }"></i>
                                    <span x-text="user.role === 'admin' ? 'Administrateur' : (user.role === 'technician' ? 'Agent' : 'Lecteur')"></span>
                                </span>
                            </td>
                            <td style="font-size:.88rem;color:var(--text-light);" x-text="user.department || '—'"></td>
                            <td style="font-size:.88rem;color:var(--text-light);" x-text="user.phone || '—'"></td>
                            <td>
                                <span class="status-badge" :class="user.is_active ? 'status-active' : 'status-danger'">
                                    <i class="fas" :class="user.is_active ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="user.is_active ? 'Actif' : 'Inactif'"></span>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Modifier" @click="editUser(user)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Activer / Désactiver"
                                            :disabled="user.is_current"
                                            :style="user.is_current ? 'opacity:.4;cursor:not-allowed;' : ''"
                                            @click="!user.is_current && toggleUserStatus(user)">
                                        <i class="fas" :class="user.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer"
                                            :disabled="user.is_current"
                                            :style="user.is_current ? 'opacity:.4;cursor:not-allowed;' : ''"
                                            @click="!user.is_current && deleteUser(user.id)">
                                        <i class="fas fa-trash" style="color:var(--danger-color);"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="users.length === 0">
                        <td colspan="7" style="padding:48px;text-align:center;color:var(--text-light);">
                            <i class="fas fa-users" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;"></i>
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>