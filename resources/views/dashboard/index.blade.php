<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NetConfig Pro · Tableau de bord</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

    <style>
        :root {
            --primary-color: #0ea5e9;
            --primary-dark: #0284c7;
            --primary-light: #38bdf8;
            --secondary-color: #475569;
            --accent-color: #8b5cf6;
            --accent-hover: #7c3aed;
            --header-bg: #0f172a;
            --header-dark: #020617;
            --header-light: #1e293b;
            --border-color: #e2e8f0;
            --background-color: #f8fafc;
            --text-color: #1e293b;
            --text-light: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --card-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
            --card-shadow-hover: 0 20px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all .3s cubic-bezier(.4,0,.2,1);
            --font-primary: 'Poppins', sans-serif;
            --font-secondary: 'Inter', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-primary);
            line-height: 1.6;
            color: var(--text-color);
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        h1,h2,h3,h4,h5,h6 { font-weight: 700; font-family: var(--font-secondary); }

        /* ── Breadcrumb ── */
        .breadcrumb {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-dark) 100%);
            color: white; padding: 14px 24px; font-size: .9rem;
            box-shadow: 0 2px 10px rgba(0,0,0,.1); position: relative; z-index: 100;
        }
        .breadcrumb a {
            color: #cbd5e1; text-decoration: none; transition: var(--transition);
            padding: 4px 8px; border-radius: 6px;
        }
        .breadcrumb a:hover { color: white; background: rgba(255,255,255,.1); }

        /* ── Header ── */
        .main-header {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-dark) 100%);
            padding: 30px 0; color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,.15); position: relative;
            /* overflow: hidden;  ← SUPPRIMÉ pour ne pas couper le menu déroulant */
        }
        .main-header::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color), var(--accent-color));
            background-size: 200% 100%; animation: shimmer 3s infinite linear;
        }
        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position:  200% 0; }
        }
        .header-content {
            max-width: 1400px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 20px; padding: 0 24px;
        }
        .header-brand { display: flex; align-items: center; gap: 16px; flex: 1; min-width: 300px; }
        .header-logo {
            width: 60px; height: 60px; border-radius: 12px; object-fit: cover;
            border: 3px solid rgba(255,255,255,.2); box-shadow: 0 4px 12px rgba(0,0,0,.2);
            transition: var(--transition);
        }
        .header-logo:hover { transform: scale(1.05); border-color: var(--accent-color); }
        .header-title { flex: 1; }
        .main-title {
            color: white; margin: 0 0 12px; font-size: 2rem; font-weight: 800;
            display: flex; align-items: center; gap: 12px; text-shadow: 0 2px 4px rgba(0,0,0,.2);
        }
        .main-title i { color: var(--accent-color); filter: drop-shadow(0 2px 4px rgba(0,0,0,.2)); }
        .subtitle {
            color: #cbd5e1; margin: 0; font-size: 1.1rem; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
        }
        .header-actions { display: flex; gap: 16px; align-items: center; }

        /* ── Profil utilisateur (menu déroulant) ── */
        .profile-menu {
            position: relative;
        }
        .profile-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 40px;
            padding: 6px 12px 6px 6px;
            cursor: pointer;
            transition: var(--transition);
            color: white;
        }
        .profile-button:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
        }
        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            color: white;
        }
        .profile-name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 280px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow-hover);
            border: 1px solid var(--border-color);
            overflow: hidden;
            z-index: 1100;  /* ← plus élevé pour passer devant tout */
            animation: fadeIn 0.2s ease;
        }
        .profile-header {
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            color: white;
        }
        .profile-header h4 {
            font-size: 1.1rem;
            margin-bottom: 4px;
        }
        .profile-header p {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .profile-role {
            display: inline-block;
            border-radius: 16px;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 6px;
        }
        .profile-role.admin {
            background: rgba(239, 68, 68, 0.9);
        }
        .profile-role.agent {
            background: rgba(245, 158, 11, 0.9);
        }
        .profile-role.viewer {
            background: rgba(59, 130, 246, 0.9);
        }
        .profile-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-color);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .profile-menu-item:hover {
            background: #f1f5f9;
        }
        .profile-menu-item i {
            width: 20px;
            color: var(--primary-color);
        }
        .profile-divider {
            height: 1px;
            background: var(--border-color);
            margin: 8px 0;
        }

        /* ── Tabs ── */
        .tabs-navigation {
            background: white; border-radius: var(--border-radius); padding: 0;
            margin: 24px auto; max-width: 1400px; box-shadow: var(--card-shadow);
            position: sticky; top: 0; z-index: 90;
        }
        .tabs-container { display: flex; overflow-x: auto; scrollbar-width: none; }
        .tabs-container::-webkit-scrollbar { display: none; }
        .tab-button {
            padding: 20px 32px; border: none; background: none;
            font-family: var(--font-secondary); font-size: 1rem; font-weight: 600;
            color: var(--text-light); cursor: pointer; transition: var(--transition);
            position: relative; white-space: nowrap; display: flex; align-items: center; gap: 10px;
        }
        .tab-button:hover { color: var(--primary-color); background: rgba(14,165,233,.05); }
        .tab-button.active { color: var(--primary-color); }
        .tab-button.active::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px 3px 0 0;
        }
        .tab-button i { font-size: 1.2em; }

        /* ── Container ── */
        .dashboard-container { max-width: 1400px; margin: 40px auto; padding: 0 24px; }

        /* ── Welcome ── */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: var(--border-radius-lg); padding: 40px; color: white;
            margin-bottom: 40px; position: relative; overflow: hidden;
            box-shadow: var(--card-shadow-hover);
        }
        .welcome-header { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
        .welcome-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2; }
        .welcome-subtitle { font-size: 1.3rem; margin-bottom: 30px; opacity: .9; line-height: 1.5; }
        .welcome-stats { display: flex; gap: 40px; margin-top: 30px; }
        .stat-value {
            font-size: 2.5rem; font-weight: 800; margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .stat-label { font-size: 1rem; opacity: .8; font-weight: 600; }

        /* ── KPI ── */
        .kpi-section { margin-bottom: 40px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 30px; }
        .kpi-card {
            background: white; border-radius: var(--border-radius); padding: 24px;
            box-shadow: var(--card-shadow); transition: var(--transition);
            border-left: 5px solid var(--primary-color); position: relative; overflow: hidden;
        }
        .kpi-card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0); transform-origin: left; transition: transform .5s ease;
        }
        .kpi-card:hover { transform: translateY(-5px); box-shadow: var(--card-shadow-hover); }
        .kpi-card:hover::before { transform: scaleX(1); }
        .kpi-icon { font-size: 2.5rem; color: var(--primary-color); margin-bottom: 16px; }
        .kpi-value { font-size: 2.2rem; font-weight: 800; color: var(--primary-color); margin-bottom: 8px; }
        .kpi-label { font-weight: 600; color: var(--secondary-color); margin-bottom: 8px; font-size: 1rem; }
        .kpi-trend { display: flex; align-items: center; gap: 6px; font-size: .9rem; color: var(--text-light); }
        .trend-up { color: var(--success-color); }
        .trend-down { color: var(--danger-color); }

        /* ── Charts ── */
        .charts-section {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px; margin-bottom: 40px;
        }
        @media (max-width: 1100px) { .charts-section { grid-template-columns: 1fr; } }
        .chart-card {
            background: white; border-radius: var(--border-radius); padding: 24px;
            box-shadow: var(--card-shadow); transition: var(--transition);
        }
        .chart-card:hover { transform: translateY(-3px); box-shadow: var(--card-shadow-hover); }
        .chart-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid var(--border-color);
            position: relative;
        }
        .chart-header::after {
            content: ""; position: absolute; bottom: -2px; left: 0; width: 80px; height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }
        .chart-title {
            color: var(--header-bg); font-size: 1.3rem; font-weight: 700;
            display: flex; align-items: center; gap: 12px;
        }
        .chart-title i { color: var(--primary-color); font-size: 1.3em; }
        .chart-container { height: 300px; position: relative; }

        /* ── Equipment section ── */
        .equipment-section {
            background: white; border-radius: var(--border-radius); padding: 24px;
            box-shadow: var(--card-shadow); margin-bottom: 40px;
        }
        .section-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid var(--border-color);
            position: relative;
        }
        .section-header::after {
            content: ""; position: absolute; bottom: -2px; left: 0; width: 80px; height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }
        .section-title {
            color: var(--header-bg); font-size: 1.5rem; font-weight: 700;
            display: flex; align-items: center; gap: 12px;
        }
        .section-title i { color: var(--primary-color); font-size: 1.3em; }
        .section-actions { display: flex; gap: 12px; }

        /* ── Table ── */
        .equipment-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
            font-size: .95rem; border-radius: var(--border-radius); overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,.05);
        }
        .equipment-table th,
        .equipment-table td { border: none; padding: 16px 20px; text-align: left; vertical-align: middle; }
        .equipment-table th {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-light) 100%);
            color: white; font-weight: 600; position: relative; font-size: .9rem;
        }
        .equipment-table th:after {
            content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px;
            background: var(--accent-color);
        }
        .equipment-table tr:nth-child(even) { background-color: #f8fafc; }
        .equipment-table tr { transition: var(--transition); }
        .equipment-table tr:hover { background-color: #f1f5f9; transform: translateY(-1px); }

        /* ── Filters ── */
        .filters-section {
            background: white; border-radius: var(--border-radius); padding: 20px;
            margin-bottom: 20px; box-shadow: var(--card-shadow);
            display: flex; gap: 16px; align-items: center; flex-wrap: wrap;
        }
        .search-box { flex: 1; min-width: 300px; position: relative; }
        .search-box input {
            width: 100%; padding: 12px 16px 12px 44px; border: 2px solid var(--border-color);
            border-radius: var(--border-radius); font-family: var(--font-secondary);
            font-size: .95rem; transition: var(--transition);
        }
        .search-box input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
        .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
        .filter-group { display: flex; gap: 12px; align-items: center; }
        .filter-select {
            padding: 10px 16px; border: 2px solid var(--border-color);
            border-radius: var(--border-radius); background: white;
            font-family: var(--font-secondary); font-size: .9rem;
            color: var(--text-color); cursor: pointer; transition: var(--transition);
        }
        .filter-select:focus { outline: none; border-color: var(--primary-color); }

        /* ── Badges ── */
        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .8rem; padding: 6px 12px; border-radius: 16px; font-weight: 600;
        }
        .status-active  { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
        .status-warning { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .status-danger  { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
        .status-info    { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #3730a3; }
        .status-offline { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); color: #475569; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
            border: none; border-radius: var(--border-radius); font-weight: 600;
            cursor: pointer; transition: var(--transition); text-decoration: none;
            font-size: .9rem; font-family: var(--font-secondary);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white; box-shadow: 0 4px 12px rgba(14,165,233,.3);
        }
        .btn-primary:hover { background: linear-gradient(135deg, var(--primary-dark) 0%, #0369a1 100%); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(14,165,233,.4); }
        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            color: white; box-shadow: 0 4px 12px rgba(139,92,246,.3);
        }
        .btn-accent:hover { background: linear-gradient(135deg, var(--accent-hover) 0%, #6d28d9 100%); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(139,92,246,.4); }
        .btn-outline { background: transparent; border: 2px solid var(--border-color); color: var(--text-color); }
        .btn-outline:hover { background: #f8fafc; border-color: var(--primary-color); color: var(--primary-color); }
        .btn-sm { padding: 6px 12px; font-size: .8rem; }
        .btn-icon { padding: 8px; width: 36px; height: 36px; justify-content: center; }
        .action-buttons { display: flex; gap: 8px; justify-content: flex-start; }

        /* ── Animations ── */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn .6s ease-in-out; }

        .spinner {
            width: 40px; height: 40px; border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color); border-radius: 50%;
            animation: spin 1s linear infinite; margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* ── Responsive ── */
        @media (max-width: 1200px) {
            .welcome-header { grid-template-columns: 1fr; gap: 30px; text-align: center; }
            .welcome-stats { justify-content: center; }
            .filters-section { flex-direction: column; align-items: stretch; }
            .search-box { min-width: 100%; }
        }
        @media (max-width: 768px) {
            .header-content { flex-direction: column; align-items: flex-start; }
            .header-brand { flex-direction: column; text-align: center; gap: 12px; }
            .header-actions { width: 100%; justify-content: flex-end; }
            .dashboard-container { padding: 0 16px; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .section-actions { width: 100%; justify-content: flex-end; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-section { grid-template-columns: 1fr; }
            .welcome-section { padding: 30px 20px; }
            .welcome-title { font-size: 2rem; }
            .welcome-stats { flex-direction: column; gap: 20px; }
            .equipment-table { display: block; overflow-x: auto; }
            .action-buttons { flex-wrap: wrap; }
        }
        @media (max-width: 480px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .main-title { font-size: 1.6rem; }
        }

        /* ── Toast ── */
        .toast-container {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            display: flex; flex-direction: column; gap: 12px;
        }
        .toast {
            padding: 14px 20px; border-radius: var(--border-radius); color: white;
            font-weight: 600; box-shadow: var(--card-shadow-hover);
            animation: fadeIn .3s ease;
        }
        .toast-success { background: var(--success-color); }
        .toast-danger  { background: var(--danger-color); }
        .toast-info    { background: var(--info-color); }
        .toast-warning { background: var(--warning-color); }

        /* ── [x-cloak] ── */
        [x-cloak] { display: none !important; }
        .avatar-base {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15), inset 0 0 0 1px rgba(255,255,255,0.3);
            border: 2px solid rgba(255,255,255,0.2);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: transform 0.2s ease;
        }
        .avatar-base:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body x-data="dashboardApp()" x-init="init()">

    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a> &gt;
        <strong><i class="fas fa-network-wired"></i> NetConfig Pro</strong>
    </div>

    <header class="main-header">
        <div class="header-content">
            <div class="header-brand">
                <img src="https://img.icons8.com/color/96/000000/network.png" alt="Logo" class="header-logo">
                <div class="header-title">
                    <h1 class="main-title"><i class="fas fa-network-wired"></i> NetConfig Pro</h1>
                    <p class="subtitle"><i class="fas fa-shield-alt"></i> Plateforme de Gestion des Configurations Réseau</p>
                </div>
            </div>
            <div class="header-actions">
                @can('create', App\Models\Site::class)
                <button class="btn btn-accent" @click="openCreateModal('site')">
                    <i class="fas fa-plus"></i> Nouveau Site
                </button>
                @endcan
                <button class="btn btn-outline" @click="exportDashboard()">
                    <i class="fas fa-download"></i> Exporter
                </button>

                {{-- Menu profil --}}
                <div class="profile-menu" @click.away="profileMenuOpen = false">
                    <div class="profile-button" @click="profileMenuOpen = !profileMenuOpen">
                        <div class="profile-avatar">
                            <span x-text="currentUser.name ? currentUser.name.charAt(0).toUpperCase() : 'U'"></span>
                        </div>
                        <span class="profile-name" x-text="currentUser.name || 'Utilisateur'"></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="profile-dropdown" x-show="profileMenuOpen" x-cloak>
                        <div class="profile-header">
                            <h4 x-text="currentUser.name"></h4>
                            <p x-text="currentUser.email"></p>
                            <span class="profile-role" :class="{
                                'admin': currentUser.role === 'admin',
                                'agent': currentUser.role === 'agent',
                                'viewer': currentUser.role === 'viewer'
                            }" x-text="currentUser.role === 'admin' ? 'Administrateur' : (currentUser.role === 'agent' ? 'Agent' : 'Observateur')"></span>
                        </div>
                        <button class="profile-menu-item" @click="editUser(currentUser); profileMenuOpen = false">
                            <i class="fas fa-user-edit"></i>
                            <span>Mon profil</span>
                        </button>
                        {{-- Lien admin vers la gestion des utilisateurs --}}
                        <template x-if="permissions.manageUsers">
                            <button class="profile-menu-item" @click="switchTab('users'); profileMenuOpen = false">
                                <i class="fas fa-users-cog"></i>
                                <span>Gestion des utilisateurs</span>
                            </button>
                        </template>
                        <div class="profile-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" style="display: block;">
                            @csrf
                            <button type="submit" class="profile-menu-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Déconnexion</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="tabs-navigation">
        <div class="tabs-container">
            <button class="tab-button" :class="{ active: currentTab === 'dashboard' }" @click="switchTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Tableau de Bord
            </button>
            @can('viewAny', App\Models\Site::class)
            <button class="tab-button" :class="{ active: currentTab === 'sites' }" @click="switchTab('sites')">
                <i class="fas fa-building"></i> Sites
            </button>
            @endcan
            @can('viewAny', App\Models\SwitchModel::class)
            <button class="tab-button" :class="{ active: currentTab === 'switches' }" @click="switchTab('switches')">
                <i class="fas fa-exchange-alt"></i> Switchs
            </button>
            @endcan
            @can('viewAny', App\Models\Router::class)
            <button class="tab-button" :class="{ active: currentTab === 'routers' }" @click="switchTab('routers')">
                <i class="fas fa-route"></i> Routeurs
            </button>
            @endcan
            @can('viewAny', App\Models\Firewall::class)
            <button class="tab-button" :class="{ active: currentTab === 'firewalls' }" @click="switchTab('firewalls')">
                <i class="fas fa-fire"></i> Firewalls
            </button>
            @endcan
            @if($can['manageUsers'] ?? false)
            <button class="tab-button" :class="{ active: currentTab === 'users' }" @click="switchTab('users')">
                <i class="fas fa-users-cog"></i> Utilisateurs
            </button>
            @endif
        </div>
    </div>

    @php
        $chartDataSafe = $chartData ?? [
            'deviceDistribution' => [
                'labels' => ['Firewalls', 'Routeurs', 'Switchs'],
                'data'   => [0, 0, 0],
                'colors' => ['#ef4444', '#10b981', '#0ea5e9'],
            ],
            'availabilityData' => [
                'labels' => ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
                'data'   => [99.2, 99.5, 99.8, 99.7, 99.6, 99.9, 99.4],
            ],
            'incidentsData' => [
                'labels' => ['Connexion','CPU','Mémoire','Bande Passante','Disque'],
                'data'   => [0, 0, 0, 0, 0],
            ],
            'loadData' => [
                'labels'    => ['00:00','04:00','08:00','12:00','16:00','20:00'],
                'firewalls' => [45, 48, 62, 68, 55, 50],
                'routers'   => [60, 58, 72, 78, 65, 62],
                'switches'  => [40, 42, 55, 58, 48, 45],
            ],
        ];

        $totalsSafe = $totals ?? [
            'sites' => 0, 'firewalls' => 0, 'routers' => 0, 'switches' => 0,
            'devices' => 0, 'availability' => 99.7, 'avgUptime' => 45, 'incidentsToday' => 0,
        ];
    @endphp

    <div class="dashboard-container">
        <div x-show="currentTab === 'dashboard'" x-cloak>
            @include('dashboard.partials.dashboard')
        </div>
        <div x-show="currentTab === 'sites'" x-cloak>
            @include('dashboard.partials.sites')
        </div>
        <div x-show="currentTab === 'switches'" x-cloak>
            @include('dashboard.partials.switches')
        </div>
        <div x-show="currentTab === 'routers'" x-cloak>
            @include('dashboard.partials.routers')
        </div>
        <div x-show="currentTab === 'firewalls'" x-cloak>
            @include('dashboard.partials.firewalls')
        </div>
    </div>

    @include('dashboard.partials.modals')

    {{-- Toast --}}
    <div class="toast-container" x-cloak>
        <div x-show="toast.show" :class="'toast toast-' + toast.type" x-text="toast.message"></div>
    </div>

    @if($can['manageUsers'] ?? false)
        <div x-show="currentTab === 'users'" x-cloak class="fade-in">
          @include('dashboard.partials.users')
        </div>
    @endif
    <script>
    function dashboardApp() {
        return {
            sites:     @json($sitesForJs ?? []),
            switches:  @json($switches   ?? []),
            routers:   @json($routers    ?? []),
            firewalls: @json($firewalls  ?? []),
            users:     @json($usersForJs ?? []),
            userTotals: @json($userTotals ?? []),
            currentUser:@json($currentUser  ?? []),
            totals:    @json($totalsSafe),
            chartData: @json($chartDataSafe),
            permissions: @json($can        ?? []),

            charts: {},

            currentTab:  'dashboard',
            currentModal: null,
            modalTitle:  '',
            modalData:   {},
            formData:    {},

            profileMenuOpen: false,

            filters: {
                sites:     { search: '' },
                switches:  { search: '', status: '', site: '' },
                routers:   { search: '', status: '', site: '' },
                firewalls: { search: '', status: '', site: '' },
            },

            toast: { show: false, message: '', type: 'info' },

            init() {
                if (this._initialized) return;   // ← AJOUTER ce verrou
                this._initialized = true;

                if (this.totals.devices === 0) {
                    this.totals.devices = this.totals.firewalls + this.totals.routers + this.totals.switches;
                }
                this.updateChartData();
                this.switchTab('dashboard');
            },

            switchTab(tab) {
                this.currentTab = tab;
                if (tab === 'dashboard') {
                    // Double nextTick + setTimeout pour laisser Alpine finir le rendu DOM
                    this.$nextTick(() => {
                        setTimeout(() => this.initCharts(), 50);
                    });
                }
            },

            updateChartData() {
                this.chartData.deviceDistribution.data = [
                    this.totals.firewalls || 0,
                    this.totals.routers   || 0,
                    this.totals.switches  || 0,
                ];
            },

            getCtx(id) {
                const el = document.getElementById(id);
                if (!el) {
                    console.warn(`Canvas #${id} introuvable dans le DOM`);
                    return null;
                }
                // Vérification que le canvas est réellement visible et a des dimensions
                if (el.offsetWidth === 0 || el.offsetHeight === 0) {
                    console.warn(`Canvas #${id} a des dimensions nulles (parent caché ?)`);
                    return null;
                }
                return el.getContext('2d');
            },

            initCharts() {
                Object.values(this.charts).forEach(c => c?.destroy());
                this.charts = {};

                const ctx1 = this.getCtx('deviceDistributionChart');
                if (ctx1) {
                    this.charts.deviceDistribution = new Chart(ctx1, {
                        type: 'pie',
                        data: {
                            labels: this.chartData.deviceDistribution.labels,
                            datasets: [{
                                data: this.chartData.deviceDistribution.data,
                                backgroundColor: this.chartData.deviceDistribution.colors,
                                borderWidth: 2, borderColor: '#ffffff',
                            }],
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { padding: 20, usePointStyle: true, font: { family: 'Inter, sans-serif', size: 12 } },
                                },
                                tooltip: {
                                    callbacks: {
                                        label(ctx) {
                                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const pct   = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                                            return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                const ctx2 = this.getCtx('availabilityChart');
                if (ctx2) {
                    this.charts.availability = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: this.chartData.availabilityData.labels,
                            datasets: [{
                                label: 'Disponibilité (%)',
                                data: this.chartData.availabilityData.data,
                                borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.1)',
                                borderWidth: 3, fill: true, tension: 0.4,
                            }],
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: false, min: 98, max: 100,
                                     ticks: { callback: v => v + '%' } },
                            },
                        },
                    });
                }

                const ctx3 = this.getCtx('incidentsChart');
                if (ctx3) {
                    this.charts.incidents = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: this.chartData.incidentsData.labels,
                            datasets: [{
                                label: "Nombre d'incidents",
                                data: this.chartData.incidentsData.data,
                                backgroundColor: ['#ef4444','#f59e0b','#0ea5e9','#10b981','#8b5cf6'],
                            }],
                        },
                        options: { responsive: true, maintainAspectRatio: false,
                                   scales: { y: { beginAtZero: true } } },
                    });
                }

                const ctx4 = this.getCtx('loadChart');
                if (ctx4) {
                    this.charts.load = new Chart(ctx4, {
                        type: 'line',
                        data: {
                            labels: this.chartData.loadData.labels,
                            datasets: [
                                { label: 'Firewalls', data: this.chartData.loadData.firewalls,
                                  borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.1)', borderWidth: 2, tension: 0.4 },
                                { label: 'Routeurs',  data: this.chartData.loadData.routers,
                                  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.1)', borderWidth: 2, tension: 0.4 },
                                { label: 'Switchs',   data: this.chartData.loadData.switches,
                                  borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.1)', borderWidth: 2, tension: 0.4 },
                            ],
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
                        },
                    });
                }
            },

            toggleChartType(chartId) {
                if (!this.charts[chartId]) return;
                const chart = this.charts[chartId];
                const types = ['pie', 'bar', 'line'];
                const nextType = types[(types.indexOf(chart.config.type) + 1) % types.length];
                
                // Sauvegarder les données
                const data = chart.config.data;
                const options = chart.config.options;
                const canvasId = chart.canvas.id;
                
                // Détruire l'ancienne instance
                chart.destroy();
                
                // Recréer avec le nouveau type
                const ctx = document.getElementById(canvasId)?.getContext('2d');
                if (ctx) {
                    this.charts[chartId] = new Chart(ctx, { type: nextType, data, options });
                }
                this.showToast(`Graphique : ${nextType}`, 'info');
            },

            // Filtres
            get filteredSites() {
                return this.sites.filter(s => {
                    if (!this.filters.sites.search) return true;
                    const q = this.filters.sites.search.toLowerCase();
                    return (s.name?.toLowerCase()    || '').includes(q)
                        || (s.address?.toLowerCase() || '').includes(q)
                        || (s.city?.toLowerCase()    || '').includes(q);
                });
            },

            get filteredSwitches() {
                return this.switches.filter(sw => {
                    const q = this.filters.switches.search.toLowerCase();
                    if (q && !(sw.name?.toLowerCase() || '').includes(q)) return false;
                    if (this.filters.switches.status && sw.status !== this.filters.switches.status) return false;
                    if (this.filters.switches.site   && sw.site   !== this.filters.switches.site)   return false;
                    return true;
                });
            },

            get filteredRouters() {
                return this.routers.filter(rt => {
                    const q = this.filters.routers.search.toLowerCase();
                    if (q && !(rt.name?.toLowerCase() || '').includes(q)) return false;
                    if (this.filters.routers.status && rt.status !== this.filters.routers.status) return false;
                    if (this.filters.routers.site   && rt.site   !== this.filters.routers.site)   return false;
                    return true;
                });
            },

            get filteredFirewalls() {
                return this.firewalls.filter(fw => {
                    const q = this.filters.firewalls.search.toLowerCase();
                    if (q && !(fw.name?.toLowerCase() || '').includes(q)) return false;
                    if (this.filters.firewalls.status && fw.status !== this.filters.firewalls.status) return false;
                    if (this.filters.firewalls.site   && fw.site   !== this.filters.firewalls.site)   return false;
                    return true;
                });
            },

            // API helper
            async refreshCsrfToken() {
                try {
                    // Récupérer un nouveau token CSRF via une requête légère
                    const res = await fetch('/csrf-token', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.token) {
                            document.querySelector('meta[name="csrf-token"]').content = data.token;
                            return data.token;
                        }
                    }
                } catch (e) { /* ignore */ }
                return null;
            },

            async apiRequest(url, method = 'GET', data = null, isRetry = false) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                };
                if (data) options.body = JSON.stringify(data);
                try {
                    const res = await fetch(url, options);
                    const contentType = res.headers.get('Content-Type') || '';

                    // ── 419 CSRF : peut arriver en JSON ou en HTML selon Laravel ──
                    // Tester le status 419 EN PREMIER, avant tout traitement du corps
                    if (res.status === 419) {
                        if (!isRetry) {
                            this.showToast('Session expirée, renouvellement en cours…', 'info');
                            const newToken = await this.refreshCsrfToken();
                            if (newToken) {
                                return this.apiRequest(url, method, data, true);
                            }
                        }
                        this.showToast('Session expirée — veuillez recharger la page', 'danger');
                        throw new Error('Session expirée (419)');
                    }

                    // ── Réponse non-JSON (HTML d'erreur, redirect login, etc.) ──
                    if (!contentType.includes('application/json')) {
                        if (res.status === 401) {
                            this.showToast('Non autorisé — veuillez recharger la page', 'danger');
                            throw new Error('Non autorisé (401)');
                        }
                        const text = await res.text();
                        console.error(`Réponse non-JSON (HTTP ${res.status}):`, text.substring(0, 500));
                        this.showToast(`Erreur serveur (HTTP ${res.status}) — voir la console`, 'danger');
                        throw new Error(`Réponse non-JSON (HTTP ${res.status})`);
                    }

                    const json = await res.json();

                    if (!res.ok) {
                        // Afficher les erreurs de validation Laravel champ par champ
                        if (res.status === 422 && json.errors) {
                            const messages = Object.values(json.errors).flat().join(' · ');
                            this.showToast(`Validation : ${messages}`, 'danger');
                            throw new Error(messages);
                        }
                        const msg = json.message || `HTTP ${res.status}`;
                        this.showToast(msg, 'danger');
                        throw new Error(msg);
                    }

                    return json;
                } catch (err) {
                    if (!err.message?.includes('Session') && !err.message?.includes('Validation')
                        && !err.message?.includes('non-JSON') && !err.message?.includes('autorisé')) {
                        console.error('API Error:', err);
                        this.showToast('Erreur de communication avec le serveur', 'danger');
                    }
                    throw err;
                }
            },

            // CRUD
            openCreateModal(type) {
                this.currentModal = 'create';
                this.modalTitle   = `Nouveau ${this.getTypeLabel(type)}`;
                this.modalData    = { type };
                this.formData     = this.getEmptyForm(type);
                this.showModal('createEquipmentModal');
            },

            getEmptyForm(type) {
                const base = { name:'', site_id:'', model:'', brand:'',
                                ip_nms:'', vlan_nms:'', ip_service:'', vlan_service:'', configuration:'' };
                if (type === 'switch')   return { ...base, ports_total: 24, vlans: 10 };
                if (type === 'router')   return { ...base, management_ip:'', interfaces_count: 24, interfaces_up_count: 22 };
                if (type === 'firewall') return { ...base, firewall_type: 'other', security_policies_count: 0, cpu: 0, memory: 0 };
                if (type === 'user')     return { name:'', email:'', password:'', password_confirmation:'', role:'agent', department:'', phone:'', is_active:true };
                return base;
            },

            getTypeLabel(type) {
                return { site:'Site', switch:'Switch', router:'Routeur', firewall:'Firewall' }[type] || type;
            },

             async saveEquipment() {
                const type = this.modalData.type;

                if (type === 'user') {
                    if (this.formData.password && this.formData.password !== this.formData.password_confirmation) {
                        this.showToast('Les mots de passe ne correspondent pas', 'danger'); return;
                    }
                    const method  = this.modalData.id ? 'PUT' : 'POST';
                    const url     = this.modalData.id ? `/api/users/${this.modalData.id}` : '/api/users';
                    // Construire un payload propre — uniquement les champs utilisateur
                    const payload = {
                        name:       this.formData.name       || '',
                        email:      this.formData.email      || '',
                        role:       this.formData.role       || 'agent',
                        department: this.formData.department || '',
                        phone:      this.formData.phone      || '',
                        // is_active peut arriver en string "true"/"false" depuis Alpine radio → convertir
                        is_active:  this.formData.is_active === true || this.formData.is_active === 'true',
                    };
                    if (this.formData.password) {
                        payload.password = this.formData.password;
                        payload.password_confirmation = this.formData.password_confirmation;
                    }
                    const result = await this.apiRequest(url, method, payload);
                    if (result.success) {
                        this.users = method === 'POST'
                            ? [...this.users, result.data]
                            : this.users.map(u => u.id === this.modalData.id ? { ...u, ...result.data } : u);
                        if (this.currentUser.id === result.data.id) this.currentUser = { ...this.currentUser, ...result.data };
                        this.showToast(`Utilisateur ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                        this.closeModal('createEquipmentModal');
                    }
                    return;
                }

                if (type === 'site') {
                    const method  = this.modalData.id ? 'PUT' : 'POST';
                    const url     = this.modalData.id ? `/api/sites/${this.modalData.id}` : '/api/sites';
                    const payload = {
                        name:              this.formData.name              || '',
                        code:              this.formData.code              || '',
                        description:       this.formData.description       || '',
                        address:           this.formData.address           || '',
                        postal_code:       this.formData.postal_code       || '',
                        city:              this.formData.city              || '',
                        country:           this.formData.country           || '',
                        technical_contact: this.formData.technical_contact || this.formData.contact_name  || '',
                        technical_email:   this.formData.technical_email   || this.formData.contact_email || '',
                        phone:             this.formData.phone             || this.formData.contact_phone || '',
                        status:            this.formData.status            || 'active',
                        capacity:          this.formData.capacity          || 50,
                        notes:             this.formData.notes             || '',
                        switches_ids:      this.formData.switches_ids      || [],
                        routers_ids:       this.formData.routers_ids       || [],
                        firewalls_ids:     this.formData.firewalls_ids     || [],
                    };
                    const result = await this.apiRequest(url, method, payload);
                    if (result.success) {
                        const siteId = result.data.id;
                        const siteForJs = {
                            ...result.data,
                            contact_name:  result.data.technical_contact || '',
                            contact_email: result.data.technical_email   || '',
                            contact_phone: result.data.phone             || '',
                            switches_count:  (payload.switches_ids  || []).length,
                            routers_count:   (payload.routers_ids   || []).length,
                            firewalls_count: (payload.firewalls_ids || []).length,
                        };
                        this.sites = method === 'POST'
                            ? [...this.sites, siteForJs]
                            : this.sites.map(s => s.id === this.modalData.id ? { ...s, ...siteForJs } : s);
                        this.totals.sites = this.sites.length;

                        // Mettre à jour le site_id local des équipements sélectionnés/désélectionnés
                        const swIds  = payload.switches_ids  || [];
                        const rtIds  = payload.routers_ids   || [];
                        const fwIds  = payload.firewalls_ids || [];
                        this.switches  = this.switches.map(e  => {
                            if (swIds.includes(e.id))  return { ...e, site_id: siteId };
                            if (method === 'PUT' && e.site_id === siteId && !swIds.includes(e.id))
                                return { ...e, site_id: null };
                            return e;
                        });
                        this.routers   = this.routers.map(e  => {
                            if (rtIds.includes(e.id))  return { ...e, site_id: siteId };
                            if (method === 'PUT' && e.site_id === siteId && !rtIds.includes(e.id))
                                return { ...e, site_id: null };
                            return e;
                        });
                        this.firewalls = this.firewalls.map(e => {
                            if (fwIds.includes(e.id))  return { ...e, site_id: siteId };
                            if (method === 'PUT' && e.site_id === siteId && !fwIds.includes(e.id))
                                return { ...e, site_id: null };
                            return e;
                        });

                        this.showToast(`Site ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                        this.closeModal('siteFormModal');
                    }
                    return;
                }

                const urlMap = {
                    switch:   { plural: 'switches',  base: '/api/switches'  },
                    router:   { plural: 'routers',   base: '/api/routers'   },
                    firewall: { plural: 'firewalls', base: '/api/firewalls' },
                };
                const cfg    = urlMap[type]; if (!cfg) return;
                const method = this.modalData.id ? 'PUT' : 'POST';
                const url    = this.modalData.id ? `${cfg.base}/${this.modalData.id}` : cfg.base;
                const payload = { ...this.formData };
                if (payload.status !== undefined) {
                    // Force la conversion en string 'active' ou 'danger' pour passer la validation Laravel
                    let isActive = (payload.status === true || payload.status === 'active' || payload.status === 'true' || payload.status === 1);
                    payload.status = isActive ? 'active' : 'danger';
                }
                const result = await this.apiRequest(url, method, payload);
                if (result.success) {
                    this[cfg.plural] = method === 'POST'
                        ? [...this[cfg.plural], result.data]
                        : this[cfg.plural].map(i => i.id === this.modalData.id ? { ...i, ...result.data } : i);
                    this.totals.devices = this.firewalls.length + this.routers.length + this.switches.length;
                    this.chartData.deviceDistribution.data = [this.totals.firewalls, this.totals.routers, this.totals.switches];
                    this.showToast(`${this.getTypeLabel(type)} ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                    this.closeModal('createEquipmentModal');
                }
            },

            viewItem(type, id) {
                const item = this[type].find(i => i.id === id);
                if (!item) return;
                this.currentModal = 'view';
                this.modalTitle   = `Détails : ${item.name}`;
                this.modalData    = { type: type.slice(0,-1), item };
                this.showModal('viewEquipmentModal');
            },

            deleteItem(type, id) {
                // Trouver le nom de l'élément pour affichage dans le modal
                const pluralMap = { sites: 'sites', switches: 'switches', routers: 'routers', firewalls: 'firewalls' };
                const items = this[pluralMap[type]] || [];
                const item  = items.find(i => i.id === id);
                this.deleteTarget = { type, id, name: item?.name || `#${id}`, label: { sites: 'site', switches: 'switch', routers: 'routeur', firewalls: 'firewall' }[type] || type };
                this.currentModal = 'confirmDelete';
                this.showModal('confirmDeleteModal');
            },

            async confirmDelete() {
                if (!this.deleteTarget) return;
                const { type, id } = this.deleteTarget;
                const urls = { sites:'/api/sites', switches:'/api/switches', routers:'/api/routers', firewalls:'/api/firewalls' };
                const url  = urls[type];
                if (!url) return;
                try {
                    const result = await this.apiRequest(`${url}/${id}`, 'DELETE');
                    if (result.success) {
                        this[type] = this[type].filter(i => i.id !== id);
                        this.showToast('Suppression réussie', 'success');
                    }
                } catch (e) { console.error('Delete error:', e); }
                finally {
                    this.closeModal('confirmDeleteModal');
                    this.deleteTarget = null;
                }
            },

            // Modals
            showModal(id) {
                const el = document.getElementById(id);
                if (el) el.style.display = 'flex';
            },

            closeModal(id) {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
                this.$nextTick(() => {
                    this.currentModal = null;
                    this.modalData    = {};
                    this.formData     = {};
                });
                this.userToToggle = null;
            },

            editItem(type, id) {
                const singularMap = { switches: 'switch', routers: 'router', firewalls: 'firewall', sites: 'site', users: 'user' };
                if (type === 'sites') {
                    const site = this.sites.find(s => s.id === id); if (!site) return;
                    this.formData = { ...site, contact_name: site.contact_name || site.technical_contact || '', contact_email: site.contact_email || site.technical_email || '', contact_phone: site.contact_phone || site.phone || '' };
                    this.siteSelectedIds = { switches: site.switches_ids || [], routers: site.routers_ids || [], firewalls: site.firewalls_ids || [] };
                    this.modalData = { type: 'site', id }; this.modalTitle = `Modifier ${site.name}`; this.currentModal = 'create';
                    this.showModal('siteFormModal'); return;
                }
                const item = this[type]?.find(i => i.id === id); if (!item) return;
                this.modalData    = { type: singularMap[type] || type.slice(0, -1), id };
                this.formData     = { ...item };
                this.currentModal = 'create';
                this.modalTitle   = `Modifier ${item.name}`;
                this.showModal('createEquipmentModal');
            },

            // Toast
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },



            // Configuration ports / interfaces / policies
            configurePorts(switchId) {
                const item = this.switches.find(s => s.id === switchId);
                if (!item) return;
                this.currentModal = 'configurePorts';
                this.modalTitle   = `Configuration des ports : ${item.name}`;
                this.modalData    = { type: 'switch', item };
                this.formData     = { portConfiguration: '' };
                this.showModal('configurePortsModal');
            },
            async savePortConfiguration() {
                const { item } = this.modalData;
                try {
                    await this.apiRequest(`/api/switches/${item.id}/port-configuration`, 'POST',
                        { configuration: this.formData.portConfiguration });
                    this.showToast('Ports mis à jour', 'success');
                    this.closeModal('configurePortsModal');
                } catch (e) { console.error(e); }
            },

            updateInterfaces(routerId) {
                const item = this.routers.find(r => r.id === routerId);
                if (!item) return;
                this.currentModal = 'updateInterfaces';
                this.modalTitle   = `Configuration : ${item.name}`;
                this.modalData    = { type: 'router', item };
                this.formData     = { interfacesConfig: '' };
                this.showModal('updateInterfacesModal');
            },
            async saveInterfacesUpdate() {
                const { item } = this.modalData;
                try {
                    await this.apiRequest(`/api/routers/${item.id}/update-interfaces`, 'POST',
                        { interfacesConfig: this.formData.interfacesConfig });
                    this.showToast('Interfaces mises à jour', 'success');
                    this.closeModal('updateInterfacesModal');
                } catch (e) { console.error(e); }
            },

            updateSecurityPolicies(firewallId) {
                const item = this.firewalls.find(f => f.id === firewallId);
                if (!item) return;
                this.currentModal = 'updateSecurityPolicies';
                this.modalTitle   = `Politiques de sécurité : ${item.name}`;
                this.modalData    = { type: 'firewall', item };
                this.formData     = { securityPolicies: '' };
                this.showModal('updateSecurityPoliciesModal');
            },
            async saveSecurityPolicies() {
                const { item } = this.modalData;
                try {
                    await this.apiRequest(`/api/firewalls/${item.id}/update-security-policies`, 'POST',
                        { policies: this.formData.securityPolicies });
                    this.showToast('Politiques mises à jour', 'success');
                    this.closeModal('updateSecurityPoliciesModal');
                } catch (e) { console.error(e); }
            },

            // Export JSON
            exportDashboard() {
                const data = JSON.stringify({ sites:this.sites, switches:this.switches,
                    routers:this.routers, firewalls:this.firewalls, totals:this.totals }, null, 2);
                const a    = Object.assign(document.createElement('a'), {
                    href: URL.createObjectURL(new Blob([data], { type: 'application/json' })),
                    download: `netconfig-export-${new Date().toISOString().slice(0,10)}.json`,
                });
                a.click();
                this.showToast('Données exportées', 'success');
            },

            // Helpers UI
            formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    const d    = new Date(dateString);
                    const diff = Date.now() - d.getTime();
                    const mins = Math.floor(diff / 60000);
                    const hrs  = Math.floor(diff / 3600000);
                    const days = Math.floor(diff / 86400000);
                    if (mins < 1)   return 'À l\'instant';
                    if (mins < 60)  return `Il y a ${mins} min`;
                    if (hrs  < 24)  return `Il y a ${hrs}h`;
                    if (days < 7)   return `Il y a ${days}j`;
                    return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
                } catch { return 'Date invalide'; }
            },

            getEquipmentIcon(type) {
                return { firewall:'fa-fire', router:'fa-route', switch:'fa-exchange-alt', site:'fa-building' }[type] || 'fa-server';
            },

            getLastAccessUser(equipment) {
                if (equipment.last_access_user) return equipment.last_access_user;
                if (equipment.access_logs?.length) {
                    const log = equipment.access_logs[0];
                    return log.user?.name || log.ip_address || 'Inconnu';
                }
                return 'Aucun accès';
            },

            getLastAccessDate(equipment) {
                return equipment.last_access_time
                    || equipment.access_logs?.[0]?.created_at
                    || equipment.updated_at;
            },

            // Modal détails
            viewEquipmentDetails(type, item) {
                this.modalData = { type, item };
                this.showModal('viewEquipmentModal');
            },

           
            renderDetails() {
                const { item } = this.modalData;
                if (!item) return '';
                const isActive = item.status === 'active' || item.status === true;
                let html = '<div class="equipment-details">';
                html += `<div class="detail-section"><h4><i class="fas fa-info-circle"></i> Infos générales</h4><div class="detail-grid">`;
                html += `<div class="detail-item"><span class="detail-label">Nom</span><span class="detail-value">${item.name}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Site</span><span class="detail-value">${item.site || 'N/A'}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Modèle</span><span class="detail-value">${item.model || 'N/A'}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Statut</span>
                          <span class="status-badge ${isActive ? 'status-active' : 'status-danger'}">${isActive ? 'Actif' : 'Inactif'}</span></div>`;
                html += '</div></div>';
                html += `<div class="detail-section"><h4><i class="fas fa-network-wired"></i> Réseau</h4><div class="detail-grid">`;
                html += `<div class="detail-item"><span class="detail-label">IP NMS</span><span class="detail-value code">${item.ip_nms || 'N/A'} (VLAN ${item.vlan_nms || 'N/A'})</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">IP Service</span><span class="detail-value code">${item.ip_service || 'N/A'} (VLAN ${item.vlan_service || 'N/A'})</span></div>`;
                if (item.management_ip) html += `<div class="detail-item"><span class="detail-label">IP Mgmt</span><span class="detail-value code">${item.management_ip}</span></div>`;
                html += '</div></div>';
                if (item.ports)             html += `<div class="detail-section"><h4><i class="fas fa-plug"></i> Ports</h4><span class="detail-value">${item.ports}, ${item.vlans || 0} VLANs</span></div>`;
                if (item.interfaces_count)  html += `<div class="detail-section"><h4><i class="fas fa-ethernet"></i> Interfaces</h4><span class="detail-value">${item.interfaces_up_count}/${item.interfaces_count} actives</span></div>`;
                if (item.cpu !== undefined) html += `<div class="detail-section"><h4><i class="fas fa-chart-line"></i> Perf</h4><div class="detail-grid"><div class="detail-item"><span class="detail-label">CPU</span><span>${item.cpu}%</span></div><div class="detail-item"><span class="detail-label">RAM</span><span>${item.memory}%</span></div></div></div>`;
                html += '</div>';
                return html;
            },

            // ✅ FIX 2 — retourner '' (chaîne vide) quand item est absent,
            //    jamais de texte visible. La div x-html="..." sera simplement vide
            //    pendant le micro-tick entre masquage et reset, sans rien afficher.
                    renderEquipmentDetails() {
                        const { item, type } = this.modalData;
                        if (!item) return '';

                        const isActive = item.status === 'active' || item.status === true;
                        const statusBadge = (active) => `<span class="status-badge ${active ? 'status-active' : 'status-danger'}"><i class="fas ${active ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${active ? 'Actif' : 'Inactif'}</span>`;
                        const field = (label, value, mono=false) => `
                            <div style="background:white;padding:12px 14px;border-radius:8px;border:1px solid #e5e7eb;">
                                <div style="font-size:.75rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">${label}</div>
                                <div style="font-weight:600;font-size:.9rem;color:#111827;${mono?'font-family:monospace;':''}">${value || '<span style="color:#9ca3af">N/A</span>'}</div>
                            </div>`;
                        const section = (title, icon, color, bg, fields) => `
                            <div style="border-radius:10px;overflow:hidden;border:1px solid ${color}30;">
                                <div style="background:${bg};padding:12px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid ${color}30;">
                                    <i class="fas ${icon}" style="color:${color};font-size:1rem;"></i>
                                    <span style="font-weight:700;color:${color};font-size:.9rem;">${title}</span>
                                </div>
                                <div style="padding:14px;background:white;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">${fields}</div>
                            </div>`;

                        let html = '<div style="display:grid;gap:16px;">';

                        // ── Header identité + statut ─────────────────────────────
                        html += `
                            <div style="display:flex;align-items:center;gap:16px;padding:16px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,var(--primary-color),var(--accent-color));display:flex;align-items:center;justify-content:center;color:white;font-size:1.4rem;flex-shrink:0;">
                                    <i class="fas ${type==='switch'?'fa-exchange-alt':type==='router'?'fa-route':'fa-fire'}"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:1.15rem;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name || 'N/A'}</div>
                                    <div style="font-size:.82rem;color:#6b7280;margin-top:2px;">${item.brand || ''} ${item.model || ''} ${item.site ? '· '+item.site : ''}</div>
                                </div>
                                <div style="flex-shrink:0;">${statusBadge(isActive)}</div>
                            </div>`;

                        // ── Réseau (commun) ──────────────────────────────────────
                        html += section('Réseau & Accès', 'fa-network-wired', '#0891b2', '#ecfeff',
                            field('IP NMS', `<code style="background:#f0f9ff;padding:2px 6px;border-radius:4px;">${item.ip_nms||'N/A'}</code>`, false) +
                            field('VLAN NMS', item.vlan_nms, false) +
                            field('IP Service', `<code style="background:#f0f9ff;padding:2px 6px;border-radius:4px;">${item.ip_service||'N/A'}</code>`, false) +
                            field('VLAN Service', item.vlan_service, false) +
                            field('Utilisateur', item.username, true) +
                            field('Mot de passe', '•'.repeat(10) + ' <span style="font-size:.7rem;color:#9ca3af">(masqué)</span>', false)
                        );

                        // ── Infos spécifiques par type ──────────────────────────
                        if (type === 'switch') {
                            // KPIs ports
                            const portsTotal = item.ports_total || 0;
                            const portsUsed  = item.ports_used  || 0;
                            const pct = portsTotal ? Math.round(portsUsed/portsTotal*100) : 0;
                            const barColor = pct > 85 ? '#dc2626' : pct > 65 ? '#f59e0b' : '#059669';
                            html += `
                                <div style="border-radius:10px;overflow:hidden;border:1px solid #6ee7b730;">
                                    <div style="background:#ecfdf5;padding:12px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #6ee7b730;">
                                        <i class="fas fa-plug" style="color:#059669;"></i>
                                        <span style="font-weight:700;color:#059669;font-size:.9rem;">Ports</span>
                                    </div>
                                    <div style="padding:14px;background:white;">
                                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:14px;">
                                            <div style="text-align:center;padding:12px;background:#ecfdf5;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#059669;">${portsTotal}</div>
                                                <div style="font-size:.72rem;color:#065f46;font-weight:600;">Total</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#eff6ff;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#2563eb;">${portsUsed}</div>
                                                <div style="font-size:.72rem;color:#1e40af;font-weight:600;">Utilisés</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#f0fdf4;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#16a34a;">${portsTotal - portsUsed}</div>
                                                <div style="font-size:.72rem;color:#15803d;font-weight:600;">Libres</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#fafafa;border-radius:8px;">
                                                <div style="font-size:1.1rem;font-weight:800;color:#374151;">${item.vlans || 0}</div>
                                                <div style="font-size:.72rem;color:#6b7280;font-weight:600;">VLANs</div>
                                            </div>
                                        </div>
                                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                            <span style="font-size:.8rem;color:#6b7280;">Taux d'utilisation</span>
                                            <span style="font-size:.8rem;font-weight:700;color:${barColor};">${pct}%</span>
                                        </div>
                                        <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
                                            <div style="height:100%;border-radius:99px;background:${barColor};width:${pct}%;transition:width .4s;"></div>
                                        </div>
                                    </div>
                                </div>`;

                            html += section('Équipement', 'fa-microchip', '#7c3aed', '#f5f3ff',
                                field('Firmware', item.firmware_version) +
                                field('N° série', item.serial_number, true) +
                                field('Asset tag', item.asset_tag, true) +
                                field('Poe', item.poe_enabled ? '✓ Activé' : '✗ Désactivé')
                            );

                        } else if (type === 'router') {
                            const intTotal = item.interfaces_count || 0;
                            const intUp    = item.interfaces_up_count || 0;
                            const intPct   = intTotal ? Math.round(intUp/intTotal*100) : 0;
                            html += `
                                <div style="border-radius:10px;overflow:hidden;border:1px solid #67e8f930;">
                                    <div style="background:#ecfeff;padding:12px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #67e8f930;">
                                        <i class="fas fa-ethernet" style="color:#0891b2;"></i>
                                        <span style="font-weight:700;color:#0891b2;font-size:.9rem;">Interfaces</span>
                                    </div>
                                    <div style="padding:14px;background:white;">
                                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:14px;">
                                            <div style="text-align:center;padding:12px;background:#ecfeff;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#0891b2;">${intTotal}</div>
                                                <div style="font-size:.72rem;color:#164e63;font-weight:600;">Total</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#ecfdf5;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#059669;">${intUp}</div>
                                                <div style="font-size:.72rem;color:#065f46;font-weight:600;">UP</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#fef2f2;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#dc2626;">${intTotal - intUp}</div>
                                                <div style="font-size:.72rem;color:#991b1b;font-weight:600;">DOWN</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#fafafa;border-radius:8px;">
                                                <div style="font-size:.85rem;font-weight:700;color:#374151;">${intPct}%</div>
                                                <div style="font-size:.72rem;color:#6b7280;font-weight:600;">Dispo.</div>
                                            </div>
                                        </div>
                                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                            <span style="font-size:.8rem;color:#6b7280;">Interfaces actives</span>
                                            <span style="font-size:.8rem;font-weight:700;color:${intPct > 50 ? '#059669' : '#dc2626'};">${intPct}%</span>
                                        </div>
                                        <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
                                            <div style="height:100%;border-radius:99px;background:${intPct > 50 ? '#059669' : '#dc2626'};width:${intPct}%;transition:width .4s;"></div>
                                        </div>
                                    </div>
                                </div>`;

                            html += section('Équipement', 'fa-microchip', '#7c3aed', '#f5f3ff',
                                field('Firmware', item.firmware_version) +
                                field('N° série', item.serial_number, true) +
                                field('Asset tag', item.asset_tag, true) +
                                field('Type routage', item.routing_protocol || 'N/A')
                            );

                        } else if (type === 'firewall') {
                            const policies = item.security_policies_count || 0;
                            const cpu      = item.cpu    || 0;
                            const mem      = item.memory || 0;
                            const cpuColor = cpu > 85 ? '#dc2626' : cpu > 65 ? '#f59e0b' : '#059669';
                            const memColor = mem > 85 ? '#dc2626' : mem > 65 ? '#f59e0b' : '#059669';
                            html += `
                                <div style="border-radius:10px;overflow:hidden;border:1px solid #fca5a530;">
                                    <div style="background:#fef2f2;padding:12px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #fca5a530;">
                                        <i class="fas fa-shield-alt" style="color:#dc2626;"></i>
                                        <span style="font-weight:700;color:#dc2626;font-size:.9rem;">Sécurité & Performance</span>
                                    </div>
                                    <div style="padding:14px;background:white;">
                                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:14px;">
                                            <div style="text-align:center;padding:12px;background:#fef2f2;border-radius:8px;">
                                                <div style="font-size:1.6rem;font-weight:800;color:#dc2626;">${policies}</div>
                                                <div style="font-size:.72rem;color:#991b1b;font-weight:600;">Règles</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#f0fdf4;border-radius:8px;">
                                                <div style="font-size:1.2rem;font-weight:800;color:${cpuColor};">${cpu}%</div>
                                                <div style="font-size:.72rem;color:#374151;font-weight:600;">CPU</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#eff6ff;border-radius:8px;">
                                                <div style="font-size:1.2rem;font-weight:800;color:${memColor};">${mem}%</div>
                                                <div style="font-size:.72rem;color:#374151;font-weight:600;">RAM</div>
                                            </div>
                                            <div style="text-align:center;padding:12px;background:#fafafa;border-radius:8px;">
                                                <div style="font-size:.8rem;font-weight:700;color:#374151;">${item.firewall_type||'N/A'}</div>
                                                <div style="font-size:.72rem;color:#6b7280;font-weight:600;">Type</div>
                                            </div>
                                        </div>
                                        <div style="display:grid;gap:8px;">
                                            <div>
                                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                                    <span style="font-size:.78rem;color:#6b7280;">CPU</span>
                                                    <span style="font-size:.78rem;font-weight:700;color:${cpuColor};">${cpu}%</span>
                                                </div>
                                                <div style="background:#e2e8f0;border-radius:99px;height:6px;overflow:hidden;">
                                                    <div style="height:100%;border-radius:99px;background:${cpuColor};width:${Math.min(cpu,100)}%;"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                                    <span style="font-size:.78rem;color:#6b7280;">RAM</span>
                                                    <span style="font-size:.78rem;font-weight:700;color:${memColor};">${mem}%</span>
                                                </div>
                                                <div style="background:#e2e8f0;border-radius:99px;height:6px;overflow:hidden;">
                                                    <div style="height:100%;border-radius:99px;background:${memColor};width:${Math.min(mem,100)}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;

                            html += section('Équipement', 'fa-microchip', '#7c3aed', '#f5f3ff',
                                field('Type firewall', item.firewall_type) +
                                field('Firmware', item.firmware_version) +
                                field('N° série', item.serial_number, true) +
                                field('HA', item.high_availability ? '✓ Actif' : '✗ Inactif') +
                                field('Dernier backup', item.last_backup ? new Date(item.last_backup).toLocaleDateString('fr-FR') : 'Jamais') +
                                field('Asset tag', item.asset_tag, true)
                            );
                        }

                        // ── Credentials (tous équipements) ──────────────────────
                        html += section('Identifiants d\'accès', 'fa-key', '#d97706', '#fffbeb',
                            field('Utilisateur', item.username, true) +
                            field('Mot de passe', '•'.repeat(12) + '<span style="font-size:.7rem;color:#9ca3af"> (masqué)</span>') +
                            field('Firmware', item.firmware_version) +
                            field('N° série', item.serial_number, true)
                        );

                        // ── Notes ────────────────────────────────────────────────
                        if (item.notes) {
                            html += `
                                <div style="border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
                                    <div style="background:#f9fafb;padding:10px 14px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                                        <i class="fas fa-sticky-note" style="color:#6b7280;"></i>
                                        <span style="font-weight:700;color:#374151;font-size:.85rem;">Notes</span>
                                    </div>
                                    <div style="padding:14px;background:white;font-size:.88rem;color:#374151;line-height:1.6;white-space:pre-wrap;">${item.notes}</div>
                                </div>`;
                        }

                        // ── Derniers accès ───────────────────────────────────────
                        if (item.access_logs?.length) {
                            html += `
                                <div style="border-radius:10px;overflow:hidden;border:1px solid #bfdbfe30;">
                                    <div style="background:#eff6ff;padding:12px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #bfdbfe;">
                                        <i class="fas fa-history" style="color:#2563eb;"></i>
                                        <span style="font-weight:700;color:#1e40af;font-size:.9rem;">Derniers accès</span>
                                    </div>
                                    <div style="padding:14px;background:white;display:grid;gap:8px;">
                                        ${item.access_logs.slice(0,5).map(log => `
                                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb;">
                                                <div style="display:flex;align-items:center;gap:10px;">
                                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--primary-color),var(--accent-color));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:.8rem;flex-shrink:0;">
                                                        ${(log.user?.name || 'U').charAt(0).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:600;font-size:.85rem;">${log.user?.name || 'Inconnu'}</div>
                                                        <div style="font-size:.75rem;color:#6b7280;">
                                                            <code style="background:#f3f4f6;padding:1px 4px;border-radius:3px;">${log.ip_address || ''}</code>
                                                            ${log.action ? `<span style="margin-left:4px;padding:1px 6px;background:#e0f2fe;border-radius:99px;color:#0369a1;">${log.action}</span>` : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style="font-size:.75rem;color:#9ca3af;white-space:nowrap;margin-left:8px;">${this.formatDate(log.created_at)}</div>
                                            </div>`).join('')}
                                        ${item.access_logs.length > 5 ? `<div style="text-align:center;font-size:.8rem;color:#9ca3af;padding:4px;">+${item.access_logs.length - 5} accès supplémentaires</div>` : ''}
                                    </div>
                                </div>`;
                        } else {
                            html += `
                                <div style="padding:20px;text-align:center;color:#9ca3af;background:#f9fafb;border-radius:10px;border:1px dashed #e5e7eb;">
                                    <i class="fas fa-history" style="display:block;margin-bottom:8px;opacity:.4;font-size:1.5rem;"></i>
                                    <span style="font-size:.85rem;">Aucun accès enregistré</span>
                                </div>`;
                        }

                        html += '</div>';
                        return html;
                    },
            modalSiteEquipmentList: [],
            modalSiteEquipmentType: null,
            modalSiteEquipmentTitle: '',
            userToToggle: null,
            deleteTarget: null,

            showSiteEquipment(siteId, type) {
                const site = this.sites.find(s => s.id === siteId);
                if (!site) return;
                let list = [];
                const typePlural = type + 's'; // 'firewalls', 'routers', 'switches'
                if (this[typePlural]) {
                    list = this[typePlural].filter(eq => eq.site_id === siteId);
                }
                this.modalSiteEquipmentList = list;
                this.modalSiteEquipmentType = type;
                const typeLabel = { firewall: 'Firewalls', router: 'Routeurs', switch: 'Switchs' }[type] || type;
                this.modalSiteEquipmentTitle = `${site.name} – ${typeLabel}`;
                this.currentModal = 'siteEquipment';
                this.showModal('viewSiteEquipmentModal');
            },
   

            editUser(user) {
                this.modalData = { type: 'user', id: user.id };
                this.formData = { ...user, password: '', password_confirmation: '' };
                this.currentModal = 'create';
                this.modalTitle = `Modifier ${user.name}`;
                this.showModal('createEquipmentModal');
            },

            async confirmToggleUserStatus() {
                if (!this.userToToggle) return;
                const user = this.userToToggle;
                const result = await this.apiRequest(`/api/users/${user.id}/toggle-status`, 'PATCH');
                if (result.success) {
                    const idx = this.users.findIndex(u => u.id === user.id);
                    if (idx !== -1) this.users[idx].is_active = result.data.is_active;
                    if (this.currentUser.id === user.id) {
                        this.currentUser.is_active = result.data.is_active;
                    }
                    this.showToast(result.message, 'success');
                }
                this.closeModal('toggleUserStatusModal');
                this.userToToggle = null;
            },
            // Pour le switch
            uploadPortConfig() {
                const fileInput = document.getElementById('portConfigFile');
                if (!fileInput.files.length) {
                    this.showToast('Veuillez sélectionner un fichier', 'warning');
                    return;
                }
                const file = fileInput.files[0];
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        // Optionnel : valider que c'est du JSON valide
                        JSON.parse(e.target.result);
                        this.formData.portConfiguration = e.target.result;
                        this.showToast('Fichier chargé avec succès', 'success');
                    } catch (err) {
                        this.showToast('Le fichier n\'est pas un JSON valide', 'danger');
                    }
                };
                reader.readAsText(file);
            },

            // Pour le firewall
            uploadSecurityPolicies() {
                const fileInput = document.getElementById('securityPoliciesFile');
                if (!fileInput.files.length) {
                    this.showToast('Veuillez sélectionner un fichier', 'warning');
                    return;
                }
                const file = fileInput.files[0];
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        JSON.parse(e.target.result);
                        this.formData.securityPolicies = e.target.result;
                        this.showToast('Fichier chargé avec succès', 'success');
                    } catch (err) {
                        this.showToast('Le fichier n\'est pas un JSON valide', 'danger');
                    }
                };
                reader.readAsText(file);
            },

            async deleteUser(id) {
                if (!confirm('Supprimer définitivement cet utilisateur ?')) return;
                const result = await this.apiRequest(`/api/users/${id}`, 'DELETE');
                if (result.success) {
                    this.users = this.users.filter(u => u.id !== id);
                    this.showToast('Utilisateur supprimé', 'success');
                }
            }
        };
    }
    </script>
</body>
</html>