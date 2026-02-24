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

        {{-- Statistiques rapides --}}
        <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
            <div class="kpi-card" style="flex:1; min-width:150px;">
                <div class="kpi-value" x-text="userTotals.total || 0"></div>
                <div class="kpi-label">Total</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:150px;">
                <div class="kpi-value" x-text="userTotals.active || 0"></div>
                <div class="kpi-label">Actifs</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:150px;">
                <div class="kpi-value" x-text="userTotals.admins || 0"></div>
                <div class="kpi-label">Administrateurs</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:150px;">
                <div class="kpi-value" x-text="userTotals.agents || 0"></div>
                <div class="kpi-label">Agents</div>
            </div>
            <div class="kpi-card" style="flex:1; min-width:150px;">
                <div class="kpi-value" x-text="userTotals.viewers || 0"></div>
                <div class="kpi-label">Viewers</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="equipment-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Département</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in users" :key="user.id">
                        <tr :class="{ 'text-muted': !user.is_active }">
                            <td>
                                <strong x-text="user.name"></strong>
                                <small x-show="user.is_current" class="status-badge status-info" style="margin-left:8px;">Vous</small>
                            </td>
                            <td x-text="user.email"></td>
                            <td>
                                <span class="status-badge" :class="{
                                    'status-danger': user.role === 'admin',
                                    'status-warning': user.role === 'agent',
                                    'status-info': user.role === 'viewer'
                                }" x-text="user.role"></span>
                            </td>
                            <td x-text="user.department || '-'"></td>
                            <td x-text="user.phone || '-'"></td>
                            <td>
                                <span class="status-badge" :class="user.is_active ? 'status-active' : 'status-danger'"
                                      x-text="user.is_active ? 'Actif' : 'Inactif'"></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Modifier"
                                            @click="editUser(user)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Activer/Désactiver"
                                            @click="toggleUserStatus(user)">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-icon" title="Supprimer"
                                            @click="deleteUser(user.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="users.length === 0">
                        <td colspan="7" style="padding:40px; text-align:center; color:var(--text-light);">
                            <i class="fas fa-users fa-3x" style="opacity:.3; display:block; margin-bottom:12px;"></i>
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>