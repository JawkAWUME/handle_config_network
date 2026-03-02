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
        <div x-show="currentTab === 'dashboard'">
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

            // Nouveautés pour la sélection d'équipements dans le modal site
            siteSelectedIds: {
                switches: [],
                routers: [],
                firewalls: []
            },

            // Pour le feedback visuel temporaire
            lastAdded: null,
            lastAddedType: null,

            filters: {
                sites:     { search: '' },
                switches:  { search: '', status: '', site: '' },
                routers:   { search: '', status: '', site: '' },
                firewalls: { search: '', status: '', site: '' },
            },

            toast: { show: false, message: '', type: 'info' },

            init() {
                if (this.totals.devices === 0) {
                    this.totals.devices = this.totals.firewalls + this.totals.routers + this.totals.switches;
                }
                this.updateChartData();
                this.switchTab('dashboard');
            },

            switchTab(tab) {
                this.currentTab = tab;
                if (tab === 'dashboard') {
                    this.$nextTick(() => {
                        this.waitForCanvasAndInit();
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
                console.log(`Canvas #${id} trouvé ?`, !!el);
                if (!el) {
                    console.warn(`Canvas #${id} introuvable dans le DOM`);
                    return null;
                }
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

            waitForCanvasAndInit(attempts = 0) {
                const canvas = document.getElementById('deviceDistributionChart');
                if (canvas && canvas.offsetWidth > 0) {
                    this.initCharts();
                    return;
                }
                if (attempts > 50) { // 5 secondes max (50 * 100ms)
                    console.warn("Canvas toujours invisible, tentative forcée");
                    this.initCharts();
                    return;
                }
                setTimeout(() => this.waitForCanvasAndInit(attempts + 1), 200);
            },

            toggleChartType(chartId) {
                if (!this.charts[chartId]) return;
                const chart = this.charts[chartId];
                const types = ['pie', 'bar', 'line'];
                const nextType = types[(types.indexOf(chart.config.type) + 1) % types.length];
                
                const data = chart.config.data;
                const options = chart.config.options;
                const canvasId = chart.canvas.id;
                
                chart.destroy();
                
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
            async apiRequest(url, method = 'GET', data = null) {
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                };
                if (data) options.body = JSON.stringify(data);
                try {
                    const res = await fetch(url, options);
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return await res.json();
                } catch (err) {
                    console.error('API Error:', err);
                    this.showToast('Erreur de communication avec le serveur', 'danger');
                    throw err;
                }
            },

            // CRUD
            openCreateModal(type) {
                if (type === 'site') {
                    this.siteSelectedIds = { switches: [], routers: [], firewalls: [] };
                }
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
                if (type === 'firewall') return { ...base, security_policies_count: 0, cpu: 0, memory: 0 };
                if (type === 'user')     return { name:'', email:'', password:'', password_confirmation:'', role:'agent', department:'', phone:'', is_active:true };
                if (type === 'site')     return {
                    name: '',
                    code: '',
                    address: '',
                    city: '',
                    country: '',
                    postal_code: '',
                    latitude: '',
                    longitude: '',
                    technical_contact: '',
                    technical_email: '',
                    phone: '',
                    description: '',
                    notes: ''
                };
                return base;
            },

            getTypeLabel(type) {
                return { site:'Site', switch:'Switch', router:'Routeur', firewall:'Firewall' }[type] || type;
            },

            async saveEquipment() {
                const type = this.modalData.type;
                if (type === 'user') {
                    const method = this.modalData.id ? 'PUT' : 'POST';
                    const url = this.modalData.id ? `/api/users/${this.modalData.id}` : '/api/users';
                    try {
                        const result = await this.apiRequest(url, method, this.formData);
                        if (result.success) {
                            if (method === 'POST') {
                                this.users.push(result.data);
                            } else {
                                const idx = this.users.findIndex(u => u.id === this.modalData.id);
                                if (idx !== -1) this.users[idx] = result.data;
                                if (this.currentUser.id === result.data.id) {
                                    this.currentUser = result.data;
                                }
                            }
                            this.showToast(`Utilisateur ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                            this.closeModal('createEquipmentModal');
                        }
                    } catch (e) { console.error(e); }
                    return;
                }

                if (type === 'site') {
                    // Ajout des équipements sélectionnés
                    this.formData.switches_ids = this.siteSelectedIds.switches;
                    this.formData.routers_ids = this.siteSelectedIds.routers;
                    this.formData.firewalls_ids = this.siteSelectedIds.firewalls;

                    const method = this.modalData.id ? 'PUT' : 'POST';
                    const url = this.modalData.id ? `/api/sites/${this.modalData.id}` : '/api/sites';
                    try {
                        const result = await this.apiRequest(url, method, this.formData);
                        if (result.success) {
                            if (method === 'POST') {
                                this.sites.push(result.data);
                            } else {
                                const idx = this.sites.findIndex(s => s.id === this.modalData.id);
                                if (idx !== -1) this.sites[idx] = result.data;

                                const siteId = this.modalData.id;
                                const newSwitchIds  = result.data.switches_ids  || [];
                                const newRouterIds  = result.data.routers_ids   || [];
                                const newFirewallIds = result.data.firewalls_ids || [];

                                this.switches.forEach(sw => {
                    if (sw.site_id === siteId && !newSwitchIds.includes(sw.id)) {
                        sw.site_id = null; // dissocié
                    } else if (newSwitchIds.includes(sw.id)) {
                        sw.site_id = siteId; // associé
                    }
                });

                // Routeurs
                this.routers.forEach(rt => {
                    if (rt.site_id === siteId && !newRouterIds.includes(rt.id)) {
                        rt.site_id = null;
                    } else if (newRouterIds.includes(rt.id)) {
                        rt.site_id = siteId;
                    }
                });

                // Firewalls
                this.firewalls.forEach(fw => {
                    if (fw.site_id === siteId && !newFirewallIds.includes(fw.id)) {
                        fw.site_id = null;
                    } else if (newFirewallIds.includes(fw.id)) {
                        fw.site_id = siteId;
                    }
                });
                      this.sites[idx].switches_count  = newSwitchIds.length;
                        this.sites[idx].routers_count   = newRouterIds.length;
                        this.sites[idx].firewalls_count = newFirewallIds.length;
                            }
                            this.showToast(`Site ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                            this.closeModal('createEquipmentModal');
                        }
                    } catch (e) { console.error(e); }
                    return;
                }

                // Équipements (switch, router, firewall)
                const map = { switch: '/api/switches', router: '/api/routers', firewall: '/api/firewalls' };
                let url = map[type];
                if (!url) return;

                const method = this.modalData.id ? 'PUT' : 'POST';
                const endpoint = this.modalData.id ? `${url}/${this.modalData.id}` : url;
                const listKey = type + 's'; // 'switches', 'routers', 'firewalls'

                try {
                    const result = await this.apiRequest(endpoint, method, this.formData);
                    if (result.success) {
                        if (method === 'POST') {
                            this[listKey].push(result.data);
                        } else {
                            const idx = this[listKey].findIndex(i => i.id === this.modalData.id);
                            if (idx !== -1) this[listKey][idx] = result.data;
                        }
                        this.showToast(`${this.getTypeLabel(type)} ${method === 'POST' ? 'créé' : 'mis à jour'}`, 'success');
                        this.closeModal('createEquipmentModal');
                    }
                } catch (e) { console.error(e); }
            },

            viewItem(type, id) {
                const item = this[type].find(i => i.id === id);
                if (!item) return;
                this.currentModal = 'view';
                this.modalTitle   = `Détails : ${item.name}`;
                this.modalData    = { type: type.slice(0,-1), item };
                this.showModal('viewEquipmentModal');
            },

            async deleteItem(type, id) {
                if (!confirm('Supprimer cet élément ?')) return;
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

            // Toast
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            testConnectivity(type, id) {
                this.showToast('Fonctionnalité de test non implémentée', 'warning');
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
                this.modalTitle   = `Interfaces : ${item.name}`;
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

            renderTestResults() {
                const results = this.modalData?.results;
                if (!results) {
                    return '<p style="text-align:center;color:var(--text-light);padding:20px;">Aucun résultat disponible</p>';
                }
                if (Array.isArray(results)) {
                    return results.map(r => `
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:15px;background:#f8fafc;border-radius:var(--border-radius);margin-bottom:10px;">
                            <strong>${r.test}</strong>
                            <span class="status-badge ${r.status === 'success' ? 'status-active' : 'status-danger'}">
                                <i class="fas ${r.status === 'success' ? 'fa-check' : 'fa-times'}"></i> ${r.message || r.status}
                            </span>
                        </div>
                    `).join('');
                }
                return `<pre>${JSON.stringify(results, null, 2)}</pre>`;
            },

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

            renderEquipmentDetails() {
                const { item, type } = this.modalData;
                if (!item) return '';

                const isActive = item.status === 'active' || item.status === true;
                let html = '<div style="display:grid;gap:24px;">';

                let generalFields = [];
                if (type === 'site') {
                    generalFields = [
                        ['Nom', item.name],
                        ['Code', item.code],
                        ['Ville', item.city],
                        ['Pays', item.country],
                    ];
                } else {
                    generalFields = [
                        ['Nom', item.name],
                        ['Site', item.site || 'N/A'],
                        ['Marque', item.brand || 'N/A'],
                        ['Modèle', item.model || 'N/A'],
                        ['N° série', item.serial_number || 'N/A'],
                    ];
                }

                html += `
                    <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--primary-color)">
                        <h4 style="color:var(--primary-color);margin-bottom:16px"><i class="fas fa-info-circle"></i> Informations générales</h4>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                            ${generalFields.map(([l, v]) => `
                                <div>
                                    <div style="font-size:.85rem;color:var(--text-light)">${l}</div>
                                    <div style="font-weight:600">${v || 'N/A'}</div>
                                </div>
                            `).join('')}
                            <div>
                                <div style="font-size:.85rem;color:var(--text-light)">Statut</div>
                                <span class="status-badge ${isActive ? 'status-active' : 'status-danger'}">
                                    <i class="fas ${isActive ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                                    ${isActive ? 'Actif' : 'Inactif'}
                                </span>
                            </div>
                        </div>
                    </div>`;

                if (type === 'site') {
                    if (item.address || item.postal_code || item.city || item.country) {
                        html += `
                            <div style="background:#f0fdf4;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--success-color)">
                                <h4 style="color:var(--success-color);margin-bottom:16px"><i class="fas fa-map-marker-alt"></i> Localisation</h4>
                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                                    ${[
                                        ['Adresse', item.address],
                                        ['Code postal', item.postal_code],
                                        ['Ville', item.city],
                                        ['Pays', item.country]
                                    ].filter(([_, v]) => v).map(([l, v]) => `
                                        <div>
                                            <div style="font-size:.85rem;color:var(--text-light)">${l}</div>
                                            <div style="font-weight:600">${v}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>`;
                    }
                     if (item.technical_contact || item.technical_email || item.phone) {
                         html += `
                            <div style="background:#fef3c7;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--warning-color)">
                                <h4 style="color:#92400e;margin-bottom:16px"><i class="fas fa-address-book"></i> Contact</h4>
                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                                    ${[
                                        ['Nom', item.technical_contact],
                                        ['Email', item.technical_email],
                                        ['Téléphone', item.phone]
                                    ].filter(([_, v]) => v).map(([l, v]) => `
                                        <div>
                                            <div style="font-size:.85rem;color:#92400e">${l}</div>
                                            <div style="font-weight:600">${v}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>`;
                      }
                    if (item.firewalls_count || item.routers_count || item.switches_count) {
                        html += `
                            <div style="background:#e0f2fe;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--info-color)">
                                <h4 style="color:#0369a1;margin-bottom:16px"><i class="fas fa-network-wired"></i> Équipements</h4>
                                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                                    <div><span class="status-badge status-danger"><i class="fas fa-fire"></i> Firewalls : ${item.firewalls_count || 0}</span></div>
                                    <div><span class="status-badge status-info"><i class="fas fa-route"></i> Routeurs : ${item.routers_count || 0}</span></div>
                                    <div><span class="status-badge status-active"><i class="fas fa-exchange-alt"></i> Switchs : ${item.switches_count || 0}</span></div>
                                </div>
                            </div>`;
                    }
                } else {
                    html += `
                        <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--warning-color)">
                            <h4 style="color:#92400e;margin-bottom:16px"><i class="fas fa-key"></i> Credentials d'accès</h4>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px">
                                <div>
                                    <div style="font-size:.85rem;color:#92400e;margin-bottom:4px;font-weight:600"><i class="fas fa-user-shield"></i> Nom d'utilisateur</div>
                                    <div style="background:white;padding:10px;border-radius:8px;font-family:monospace;font-weight:600">
                                        ${item.username || '<span style="color:var(--text-light)">Non configuré</span>'}
                                    </div>
                                </div>
                                <div x-data="{ showPass: false }">
                                    <div style="font-size:.85rem;color:#92400e;margin-bottom:4px;font-weight:600"><i class="fas fa-lock"></i> Mot de passe</div>
                                    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; background:white; padding:8px 12px; border-radius:8px; font-family:monospace; font-weight:600;">
                                        <span x-show="!showPass" style="flex:1 1 auto; word-break:break-word;">${'•'.repeat(12)}</span>
                                        <span x-show="showPass" style="flex:1 1 auto; word-break:break-word; color:var(--text-light);">${item.password}</span>
                                        <button @click="showPass = !showPass" class="btn btn-outline btn-sm" style="padding:2px 8px; flex-shrink:0;">
                                            <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                    if (item.access_logs?.length) {
                        html += `
                            <div style="background:#e0f2fe;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--info-color)">
                                <h4 style="color:#0369a1;margin-bottom:16px"><i class="fas fa-history"></i> Derniers accès</h4>
                                <div style="display:grid;gap:10px">
                                ${item.access_logs.slice(0,5).map(log => `
                                    <div style="background:white;padding:12px;border-radius:8px;display:flex;justify-content:space-between;align-items:center">
                                        <div style="display:flex;align-items:center;gap:12px">
                                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary-color),var(--accent-color));display:flex;align-items:center;justify-content:center;color:white;font-weight:700">
                                                ${(log.user?.name || 'U').charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <div style="font-weight:600">${log.user?.name || log.ip_address || 'Inconnu'}</div>
                                                <div style="font-size:.8rem;color:var(--text-light)">
                                                    <i class="fas fa-desktop"></i> ${log.ip_address || ''}
                                                    ${log.action ? `• ${log.action}` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div style="font-size:.8rem;color:var(--text-light)">${this.formatDate(log.created_at)}</div>
                                    </div>`).join('')}
                                </div>
                                ${item.access_logs.length > 5 ? `<div style="text-align:center;margin-top:12px;color:var(--text-light);font-size:.85rem">... et ${item.access_logs.length - 5} accès supplémentaires</div>` : ''}
                            </div>`;
                    } else {
                        html += `
                            <div style="background:#f8fafc;padding:20px;border-radius:var(--border-radius);border-left:4px solid var(--text-light)">
                                <h4 style="color:var(--text-light);margin-bottom:12px"><i class="fas fa-history"></i> Derniers accès</h4>
                                <p style="color:var(--text-light);text-align:center;padding:20px"><i class="fas fa-info-circle"></i> Aucun accès enregistré</p>
                            </div>`;
                    }
                }

                html += '</div>';
                return html;
            },

            modalSiteEquipmentList: [],
            modalSiteEquipmentType: null,
            modalSiteEquipmentTitle: '',
            userToToggle: null,

            showSiteEquipment(siteId, type) {
                const site = this.sites.find(s => s.id === siteId);
                if (!site) return;
                let list = [];
                const typePlural = type + 's';
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

            toggleUserStatus(user) {
                this.userToToggle = user;
                this.currentModal = 'toggleUserStatus';
                this.modalTitle = 'Confirmation';
                this.showModal('toggleUserStatusModal');
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
                        JSON.parse(e.target.result);
                        this.formData.portConfiguration = e.target.result;
                        this.showToast('Fichier chargé avec succès', 'success');
                    } catch (err) {
                        this.showToast('Le fichier n\'est pas un JSON valide', 'danger');
                    }
                };
                reader.readAsText(file);
            },

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

            editItem(type, id) {
                if (type === 'sites') {
                    const site = this.sites.find(s => s.id === id);
                    if (!site) return;
                    this.formData = { ...site };
                    this.siteSelectedIds = {
                        switches: site.switches_ids || [],
                        routers: site.routers_ids || [],
                        firewalls: site.firewalls_ids || []
                    };
                    this.modalData = { type: 'site', id };
                    this.modalTitle = `Modifier ${site.name}`;
                    this.currentModal = 'create';
                    this.showModal('createEquipmentModal');
                    return;
                }
                const item = this[type].find(i => i.id === id);
                if (!item) return;
                this.modalData = { type: type.slice(0, -1), id };
                this.formData = { ...item };
                this.currentModal = 'create';
                this.modalTitle = `Modifier ${item.name}`;
                this.showModal('createEquipmentModal');
            },

            editEquipment(type, id) {
                this.editItem(type + 's', id);
            },

            async deleteUser(id) {
                if (!confirm('Supprimer définitivement cet utilisateur ?')) return;
                const result = await this.apiRequest(`/api/users/${id}`, 'DELETE');
                if (result.success) {
                    this.users = this.users.filter(u => u.id !== id);
                    this.showToast('Utilisateur supprimé', 'success');
                }
            },

            // Nouvelle méthode pour toggle équipement site (feedback)
            toggleSiteEquipment(type, id, name) {
                const list = this.siteSelectedIds[type];
                const idx = list.indexOf(id);
                if (idx === -1) {
                    list.push(id);
                    this.lastAdded = name;
                    this.lastAddedType = type;
                    setTimeout(() => { this.lastAdded = null; this.lastAddedType = null; }, 2500);
                } else {
                    list.splice(idx, 1);
                }
            },

            isSiteEquipmentSelected(type, id) {
                return this.siteSelectedIds[type].includes(id);
            },

            totalSelectedEquipment() {
                return this.siteSelectedIds.switches.length
                     + this.siteSelectedIds.routers.length
                     + this.siteSelectedIds.firewalls.length;
            }
        };
    }
    </script>
</body>
</html>
