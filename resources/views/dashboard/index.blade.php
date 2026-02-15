<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NetConfig Pro · Tableau de bord</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        /* Variables CSS modernisées */
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
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --font-primary: 'Poppins', sans-serif;
            --font-secondary: 'Inter', sans-serif;
        }

        /* Styles généraux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-primary);
            line-height: 1.6;
            color: var(--text-color);
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 700;
            font-family: var(--font-secondary);
        }

        /* Barre de navigation */
        .breadcrumb {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-dark) 100%);
            color: white;
            padding: 14px 24px;
            font-size: 0.9rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .breadcrumb a {
            color: #cbd5e1;
            text-decoration: none;
            transition: var(--transition);
            padding: 4px 8px;
            border-radius: 6px;
        }

        .breadcrumb a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        /* En-tête principal */
        .main-header {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-dark) 100%);
            padding: 30px 0;
            color: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }

        .main-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color), var(--accent-color));
            background-size: 200% 100%;
            animation: shimmer 3s infinite linear;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 0 24px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 300px;
        }

        .header-logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }

        .header-logo:hover {
            transform: scale(1.05);
            border-color: var(--accent-color);
        }

        .header-title {
            flex: 1;
        }

        .main-title {
            color: white;
            margin: 0 0 12px 0;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .main-title i {
            color: var(--accent-color);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .subtitle {
            color: #cbd5e1;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-actions {
            display: flex;
            gap: 16px;
        }

        /* Navigation par onglets */
        .tabs-navigation {
            background: white;
            border-radius: var(--border-radius);
            padding: 0;
            margin: 24px auto;
            max-width: 1400px;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .tabs-container {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .tabs-container::-webkit-scrollbar {
            display: none;
        }

        .tab-button {
            padding: 20px 32px;
            border: none;
            background: none;
            font-family: var(--font-secondary);
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tab-button:hover {
            color: var(--primary-color);
            background: rgba(14, 165, 233, 0.05);
        }

        .tab-button.active {
            color: var(--primary-color);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px 3px 0 0;
        }

        .tab-button i {
            font-size: 1.2em;
        }

        /* Conteneur principal */
        .dashboard-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 24px;
        }

        /* Section de bienvenue */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: var(--border-radius-lg);
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--card-shadow-hover);
        }

        .welcome-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .welcome-text {
            max-width: 100%;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            line-height: 1.2;
        }

        .welcome-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .welcome-stats {
            display: flex;
            gap: 40px;
            margin-top: 30px;
        }

        .welcome-stat {
            text-align: left;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 600;
        }

        /* Contenu des onglets */
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        /* Section des KPI */
        .kpi-section {
            margin-bottom: 40px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border-left: 5px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        .kpi-card:hover::before {
            transform: scaleX(1);
        }

        .kpi-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 16px;
        }

        .kpi-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .kpi-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .kpi-trend {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .trend-up {
            color: var(--success-color);
        }

        .trend-down {
            color: var(--danger-color);
        }

        /* Section des graphiques */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1100px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .chart-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-shadow-hover);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
            position: relative;
        }

        .chart-header::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }

        .chart-title {
            color: var(--header-bg);
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chart-title i {
            color: var(--primary-color);
            font-size: 1.3em;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Section des équipements */
        .equipment-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
            position: relative;
        }

        .section-header::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }

        .section-title {
            color: var(--header-bg);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary-color);
            font-size: 1.3em;
        }

        .section-actions {
            display: flex;
            gap: 12px;
        }

        /* Tableaux */
        .equipment-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.95rem;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .equipment-table th,
        .equipment-table td {
            border: none;
            padding: 16px 20px;
            text-align: left;
            vertical-align: middle;
        }

        .equipment-table th {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-light) 100%);
            color: white;
            font-weight: 600;
            text-align: left;
            position: relative;
            font-size: 0.9rem;
        }

        .equipment-table th:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent-color);
        }

        .equipment-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .equipment-table tr {
            transition: var(--transition);
        }

        .equipment-table tr:hover {
            background-color: #f1f5f9;
            transform: translateY(-1px);
        }

        /* Filtres et recherche */
        .filters-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: var(--font-secondary);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .filter-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            background: white;
            font-family: var(--font-secondary);
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        /* Badges et statuts */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 16px;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .status-warning {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .status-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .status-info {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #3730a3;
        }

        .status-offline {
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            color: #475569;
        }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 0.9rem;
            font-family: var(--font-secondary);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0369a1 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-accent:hover {
            background: linear-gradient(135deg, var(--accent-hover) 0%, #6d28d9 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-color);
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-icon {
            padding: 8px;
            width: 36px;
            height: 36px;
            justify-content: center;
        }

        /* Actions des tableaux */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
        }

        .page-btn {
            padding: 8px 16px;
            border: 2px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            font-family: var(--font-secondary);
            font-weight: 600;
            color: var(--text-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .page-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .page-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Animation de chargement */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        .loading {
            text-align: center;
            padding: 60px;
            color: var(--text-light);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .welcome-header {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }
            .welcome-stats {
                justify-content: center;
            }
            .welcome-stat {
                text-align: center;
            }
            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-brand {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }
            .dashboard-container {
                padding: 0 16px;
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .section-actions {
                width: 100%;
                justify-content: flex-end;
            }
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-section {
                grid-template-columns: 1fr;
            }
            .welcome-section {
                padding: 30px 20px;
            }
            .welcome-title {
                font-size: 2rem;
            }
            .welcome-subtitle {
                font-size: 1.1rem;
            }
            .welcome-stats {
                flex-direction: column;
                gap: 20px;
            }
            .stat-value {
                font-size: 2rem;
            }
            .tab-button {
                padding: 15px 20px;
            }
            .equipment-table {
                display: block;
                overflow-x: auto;
            }
            .action-buttons {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            .main-title {
                font-size: 1.6rem;
            }
            .breadcrumb {
                padding: 12px 16px;
                font-size: 0.8rem;
            }
            .header-content {
                padding: 0 16px;
            }
            .header-logo {
                width: 50px;
                height: 50px;
            }
            .section-title {
                font-size: 1.3rem;
            }
            .tab-button {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            .tab-button i {
                font-size: 1em;
            }
        }
    </style>
</head>
<body class="bg-gray-50" x-data="dashboardApp()" x-init="init()">

    <!-- Barre de navigation -->
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a> &gt;
        <strong><i class="fas fa-network-wired"></i> NetConfig Pro</strong>
    </div>

    <!-- En-tête principal -->
    <header class="main-header">
        <div class="header-content">
            <div class="header-brand">
                <img src="https://img.icons8.com/color/96/000000/network.png" alt="Logo NetConfig Pro" class="header-logo">
                <div class="header-title">
                    <h1 class="main-title">
                        <i class="fas fa-network-wired"></i> NetConfig Pro
                    </h1>
                    <p class="subtitle">
                        <i class="fas fa-shield-alt"></i> Plateforme de Gestion des Configurations Réseau
                    </p>
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
            </div>
        </div>
    </header>

    <!-- Navigation par onglets -->
    <div class="tabs-navigation">
        <div class="tabs-container">
            <button class="tab-button" :class="{ 'active': currentTab === 'dashboard' }" @click="switchTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Tableau de Bord
            </button>
            @can('viewAny', App\Models\Site::class)
            <button class="tab-button" :class="{ 'active': currentTab === 'sites' }" @click="switchTab('sites')">
                <i class="fas fa-building"></i> Sites
            </button>
            @endcan
            @can('viewAny', App\Models\SwitchModel::class)
            <button class="tab-button" :class="{ 'active': currentTab === 'switches' }" @click="switchTab('switches')">
                <i class="fas fa-exchange-alt"></i> Switchs
            </button>
            @endcan
            @can('viewAny', App\Models\Router::class)
            <button class="tab-button" :class="{ 'active': currentTab === 'routers' }" @click="switchTab('routers')">
                <i class="fas fa-route"></i> Routeurs
            </button>
            @endcan
            @can('viewAny', App\Models\Firewall::class)
            <button class="tab-button" :class="{ 'active': currentTab === 'firewalls' }" @click="switchTab('firewalls')">
                <i class="fas fa-fire"></i> Firewalls
            </button>
            @endcan
        </div>
    </div>
    @php
        $chartTotalsSafe = $chartTotals ?? [
        'sites' => 0,
        'firewalls' => 0,
        'routers' => 0,
        'switches' => 0,
        'devices' => 0,
        'availability' => 99.7,
        'avgUptime' => 45,
        'incidentsToday' => 0
    ];
    
    $chartDataSafe = $chartData ?? [
        'deviceDistribution' => [
            'labels' => ['Firewalls', 'Routeurs', 'Switchs'],
            'data' => [0, 0, 0],
            'colors' => ['#ef4444', '#10b981', '#0ea5e9']
        ],
        'availabilityData' => [
            'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'data' => [99.2, 99.5, 99.8, 99.7, 99.6, 99.9, 99.4]
        ],
        'incidentsData' => [
            'labels' => ['Connexion', 'CPU', 'Mémoire', 'Bande Passante', 'Disque'],
            'data' => [0, 0, 0, 0, 0]
        ],
        'loadData' => [
            'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            'firewalls' => [45, 48, 62, 68, 55, 50],
            'routers' => [60, 58, 72, 78, 65, 62],
            'switches' => [40, 42, 55, 58, 48, 45]
        ]
    ];
        @endphp

    <!-- Contenu dynamique -->
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

    <!-- Modals (création, détails, configuration, test) -->
    @include('dashboard.partials.modals')

    <script>
    function dashboardApp() {
        return {
            // Data from Laravel (avec valeurs par défaut pour éviter les erreurs)
            sites: @json($sites ?? []),
            switches: @json($switches ?? []),
            routers: @json($routers ?? []),
            firewalls: @json($firewalls ?? []),
            totals: @json($totals ?? $chartTotalsSafe),
            chartData: @json($chartData ?? $chartDataSafe),
            permissions: @json($can ?? []),
            
            charts: {},
            
            // UI state
            currentTab: 'dashboard',
            currentModal: null,
            modalTitle: '',
            modalData: {},
            formData: {},
            filters: {
                sites: { search: '' },
                switches: { search: '', status: '', site: '' },
                routers: { search: '', status: '', site: '' },
                firewalls: { search: '', status: '', site: '' }
            },

            // Toast
            toast: { show: false, message: '', type: 'info' },

            // ------------------------------------------------------------
            // Initialisation
            // ------------------------------------------------------------
            init() {
                console.log('Dashboard App Initialized');
                console.log('Totals:', this.totals);
                console.log('Sites:', this.sites);
                console.log('Firewalls:', this.firewalls);
                console.log('Routers:', this.routers);
                console.log('Switches:', this.switches);
                
                // Recalculer les totaux si nécessaire
                if (this.totals.devices === 0) {
                    this.totals.devices = this.totals.firewalls + this.totals.routers + this.totals.switches;
                }
                
                // Mettre à jour les données des graphiques
                this.updateChartData();
                
                // Initialiser les graphiques
                this.switchTab('dashboard');
            },

            // ------------------------------------------------------------
            // Changement d'onglet
            // ------------------------------------------------------------
            switchTab(tab) {
                this.currentTab = tab;
                if (tab === 'dashboard') {
                    this.$nextTick(() => this.initCharts());
                }
            },

            // ------------------------------------------------------------
            // Mettre à jour les données des graphiques
            // ------------------------------------------------------------
            updateChartData() {
                // Mettre à jour la répartition des équipements
                this.chartData.deviceDistribution.data = [
                    this.totals.firewalls || 0,
                    this.totals.routers || 0,
                    this.totals.switches || 0
                ];
                
                // Mettre à jour les incidents (simulé basé sur les totaux)
                if (this.totals.incidentsToday > 0) {
                    this.chartData.incidentsData.data = [
                        this.totals.incidentsToday,
                        Math.max(0, this.totals.incidentsToday - 2),
                        Math.max(0, this.totals.incidentsToday - 3),
                        Math.max(0, this.totals.incidentsToday - 1),
                        Math.max(0, this.totals.incidentsToday - 4)
                    ];
                }
            },

            // ------------------------------------------------------------
            // Graphiques (Chart.js)
            // ------------------------------------------------------------
            initCharts() {
                // Détruire les anciens graphiques s'ils existent
                Object.values(this.charts).forEach(chart => chart?.destroy());
                this.charts = {};

                // Répartition des équipements
                const ctx1 = document.getElementById('deviceDistributionChart')?.getContext('2d');
                if (ctx1) {
                    this.charts.deviceDistribution = new Chart(ctx1, {
                        type: 'pie',
                        data: {
                            labels: this.chartData.deviceDistribution.labels,
                            datasets: [{
                                data: this.chartData.deviceDistribution.data,
                                backgroundColor: this.chartData.deviceDistribution.colors,
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            family: 'Inter, sans-serif',
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Disponibilité hebdomadaire
                const ctx2 = document.getElementById('availabilityChart')?.getContext('2d');
                if (ctx2) {
                    this.charts.availability = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: this.chartData.availabilityData.labels,
                            datasets: [{
                                label: 'Disponibilité (%)',
                                data: this.chartData.availabilityData.data,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14,165,233,0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { 
                                y: { 
                                    beginAtZero: false, 
                                    min: 98, 
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                } 
                            }
                        }
                    });
                }

                // Incidents
                const ctx3 = document.getElementById('incidentsChart')?.getContext('2d');
                if (ctx3) {
                    this.charts.incidents = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: this.chartData.incidentsData.labels,
                            datasets: [{
                                label: "Nombre d'incidents",
                                data: this.chartData.incidentsData.data,
                                backgroundColor: ['#ef4444','#f59e0b','#0ea5e9','#10b981','#8b5cf6']
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Charge des équipements
                const ctx4 = document.getElementById('loadChart')?.getContext('2d');
                if (ctx4) {
                    this.charts.load = new Chart(ctx4, {
                        type: 'line',
                        data: {
                            labels: this.chartData.loadData.labels,
                            datasets: [
                                { 
                                    label: 'Firewalls', 
                                    data: this.chartData.loadData.firewalls, 
                                    borderColor: '#ef4444', 
                                    backgroundColor: 'rgba(239,68,68,0.1)', 
                                    borderWidth: 2,
                                    tension: 0.4 
                                },
                                { 
                                    label: 'Routeurs', 
                                    data: this.chartData.loadData.routers, 
                                    borderColor: '#10b981', 
                                    backgroundColor: 'rgba(16,185,129,0.1)', 
                                    borderWidth: 2,
                                    tension: 0.4 
                                },
                                { 
                                    label: 'Switchs', 
                                    data: this.chartData.loadData.switches, 
                                    borderColor: '#0ea5e9', 
                                    backgroundColor: 'rgba(14,165,233,0.1)', 
                                    borderWidth: 2,
                                    tension: 0.4 
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { 
                                y: { 
                                    beginAtZero: true, 
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                } 
                            }
                        }
                    });
                }
            },

            // ------------------------------------------------------------
            // Toggle Chart Type
            // ------------------------------------------------------------
            toggleChartType(chartId) {
                if (!this.charts[chartId]) {
                    console.error('Chart not found:', chartId);
                    return;
                }
                
                const chart = this.charts[chartId];
                const currentType = chart.config.type;
                let newType = 'bar';
                
                if (currentType === 'bar') {
                    newType = 'line';
                } else if (currentType === 'line') {
                    newType = 'pie';
                } else {
                    newType = 'bar';
                }
                
                chart.config.type = newType;
                chart.update();
                
                this.showToast(`Graphique changé en ${newType}`, 'info');
            },

            // ------------------------------------------------------------
            // Filtres
            // ------------------------------------------------------------
            get filteredSites() {
                return this.sites.filter(s => {
                    if (!this.filters.sites.search) return true;
                    const search = this.filters.sites.search.toLowerCase();
                    return (s.name?.toLowerCase() || '').includes(search)
                        || (s.address?.toLowerCase() || '').includes(search)
                        || (s.city?.toLowerCase() || '').includes(search);
                });
            },
            
            get filteredSwitches() {
                return this.switches.filter(sw => {
                    if (this.filters.switches.search && !(sw.name?.toLowerCase() || '').includes(this.filters.switches.search.toLowerCase())) return false;
                    if (this.filters.switches.status && sw.status !== this.filters.switches.status) return false;
                    if (this.filters.switches.site && sw.site !== this.filters.switches.site) return false;
                    return true;
                });
            },
            
            get filteredRouters() {
                return this.routers.filter(rt => {
                    if (this.filters.routers.search && !(rt.name?.toLowerCase() || '').includes(this.filters.routers.search.toLowerCase())) return false;
                    if (this.filters.routers.status && ((this.filters.routers.status === 'active' && !rt.status) || (this.filters.routers.status === 'inactive' && rt.status))) return false;
                    if (this.filters.routers.site && rt.site !== this.filters.routers.site) return false;
                    return true;
                });
            },
            
            get filteredFirewalls() {
                return this.firewalls.filter(fw => {
                    if (this.filters.firewalls.search && !(fw.name?.toLowerCase() || '').includes(this.filters.firewalls.search.toLowerCase())) return false;
                    if (this.filters.firewalls.status && ((this.filters.firewalls.status === 'active' && !fw.status) || (this.filters.firewalls.status === 'inactive' && fw.status))) return false;
                    if (this.filters.firewalls.site && fw.site !== this.filters.firewalls.site) return false;
                    return true;
                });
            },

            // ------------------------------------------------------------
            // API request helper
            // ------------------------------------------------------------
            async apiRequest(url, method = 'GET', data = null) {
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                };
                if (data) options.body = JSON.stringify(data);
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return await response.json();
                } catch (error) {
                    console.error('API Error:', error);
                    this.showToast('Erreur de communication avec le serveur', 'danger');
                    throw error;
                }
            },

            // ------------------------------------------------------------
            // Création / Édition
            // ------------------------------------------------------------
            openCreateModal(type) {
                this.currentModal = 'create';
                this.modalTitle = `Nouveau ${this.getTypeLabel(type)}`;
                this.modalData = { type };
                this.formData = this.getEmptyForm(type);
                this.showModal('createEquipmentModal');
            },

            getEmptyForm(type) {
                const base = {
                    name: '', site_id: '', model: '', brand: '',
                    ip_nms: '', vlan_nms: '', ip_service: '', vlan_service: '',
                    configuration: ''
                };
                if (type === 'switch') {
                    return { ...base, ports: 24, vlans: 10 };
                }
                if (type === 'router') {
                    return { ...base, management_ip: '', interfaces_count: 24, interfaces_up_count: 22 };
                }
                if (type === 'firewall') {
                    return { ...base, security_policies_count: 0, cpu: 0, memory: 0 };
                }
                return base;
            },

            getTypeLabel(type) {
                const labels = { site: 'Site', switch: 'Switch', router: 'Routeur', firewall: 'Firewall' };
                return labels[type] || type;
            },

            async saveEquipment() {
                let url = '', resource = '';
                const type = this.modalData.type;
                if (type === 'switch') {
                    url = '/api/switches';
                    resource = 'switches';
                } else if (type === 'router') {
                    url = '/api/routers';
                    resource = 'routers';
                } else if (type === 'firewall') {
                    url = '/api/firewalls';
                    resource = 'firewalls';
                } else {
                    return;
                }

                const method = this.modalData.id ? 'PUT' : 'POST';
                if (method === 'PUT') url = `${url}/${this.modalData.id}`;

                try {
                    const result = await this.apiRequest(url, method, this.formData);
                    if (result.success || result.data) {
                        const newItem = result.data || result;
                        if (method === 'POST') {
                            this[resource].push(newItem);
                        } else {
                            const index = this[resource].findIndex(i => i.id === this.modalData.id);
                            if (index !== -1) this[resource][index] = newItem;
                        }
                        this.showToast(`${this.getTypeLabel(type)} ${method === 'POST' ? 'créé' : 'mis à jour'} avec succès`, 'success');
                        this.closeModal('createEquipmentModal');
                    }
                } catch (e) {
                    console.error('Save error:', e);
                }
            },

            // ------------------------------------------------------------
            // Visualisation
            // ------------------------------------------------------------
            viewItem(type, id) {
                const item = this[type].find(i => i.id === id);
                if (!item) return;
                this.currentModal = 'view';
                this.modalTitle = `Détails du ${this.getTypeLabel(type.slice(0,-1))}: ${item.name}`;
                this.modalData = { type: type.slice(0,-1), item };
                this.showModal('viewEquipmentModal');
            },

            renderDetails() {
                const item = this.modalData.item;
                if (!item) return '';
                let html = '<div class="equipment-details">';
                html += `<div class="detail-section"><h4><i class="fas fa-info-circle"></i> Informations générales</h4><div class="detail-grid">`;
                html += `<div class="detail-item"><span class="detail-label">Nom</span><span class="detail-value">${item.name}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Site</span><span class="detail-value">${item.site || 'N/A'}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Modèle</span><span class="detail-value">${item.model || 'N/A'}</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">Statut</span><span class="status-badge ${item.status ? 'status-active' : 'status-danger'}">${item.status ? 'Actif' : 'Inactif'}</span></div>`;
                html += `</div></div>`;
                html += `<div class="detail-section"><h4><i class="fas fa-network-wired"></i> Configuration réseau</h4><div class="detail-grid">`;
                html += `<div class="detail-item"><span class="detail-label">IP NMS</span><span class="detail-value code">${item.ip_nms || 'N/A'} (VLAN ${item.vlan_nms || 'N/A'})</span></div>`;
                html += `<div class="detail-item"><span class="detail-label">IP Service</span><span class="detail-value code">${item.ip_service || 'N/A'} (VLAN ${item.vlan_service || 'N/A'})</span></div>`;
                if (item.management_ip) {
                    html += `<div class="detail-item"><span class="detail-label">IP Management</span><span class="detail-value code">${item.management_ip}</span></div>`;
                }
                html += `</div></div>`;
                if (item.security_policies_count !== undefined) {
                    html += `<div class="detail-section"><h4><i class="fas fa-shield-alt"></i> Politiques de sécurité</h4><span class="detail-value">${item.security_policies_count}</span></div>`;
                }
                if (item.ports) {
                    html += `<div class="detail-section"><h4><i class="fas fa-plug"></i> Ports / VLANs</h4><span class="detail-value">${item.ports} ports, ${item.vlans || 0} VLANs</span></div>`;
                }
                if (item.interfaces_count) {
                    html += `<div class="detail-section"><h4><i class="fas fa-ethernet"></i> Interfaces</h4><span class="detail-value">${item.interfaces_up_count}/${item.interfaces_count} actives</span></div>`;
                }
                if (item.cpu !== undefined) {
                    html += `<div class="detail-section"><h4><i class="fas fa-chart-line"></i> Performance</h4><div class="detail-grid">`;
                    html += `<div class="detail-item"><span class="detail-label">CPU</span><span class="detail-value">${item.cpu}%</span></div>`;
                    html += `<div class="detail-item"><span class="detail-label">Mémoire</span><span class="detail-value">${item.memory}%</span></div>`;
                    html += `</div></div>`;
                }
                html += '</div>';
                return html;
            },

            // ------------------------------------------------------------
            // Suppression
            // ------------------------------------------------------------
            async deleteItem(type, id) {
                if (!confirm(`Supprimer cet élément ?`)) return;
                let url = '';
                if (type === 'sites') url = `/api/sites/${id}`;
                else if (type === 'switches') url = `/api/switches/${id}`;
                else if (type === 'routers') url = `/api/routers/${id}`;
                else if (type === 'firewalls') url = `/api/firewalls/${id}`;
                if (!url) return;
                try {
                    const result = await this.apiRequest(url, 'DELETE');
                    if (result.success) {
                        this[type] = this[type].filter(i => i.id !== id);
                        this.showToast('Suppression réussie', 'success');
                    }
                } catch (e) {
                    console.error('Delete error:', e);
                }
            },

            // ------------------------------------------------------------
            // Gestion des modals
            // ------------------------------------------------------------
            showModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                }
            },
            
            closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                }
                this.currentModal = null;
                this.modalData = {};
                this.formData = {};
            },

            // ------------------------------------------------------------
            // Toast
            // ------------------------------------------------------------
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3000);
            },
            async testConnectivity(type, id) {
    const item = this[type + 's']?.find(i => i.id === id);
    if (!item) return;
    
    let url = '';
    if (type === 'switch') url = `/api/switches/${id}/test-connectivity`;
    else if (type === 'router') url = `/api/routers/${id}/test-connectivity`;
    else if (type === 'firewall') url = `/api/firewalls/${id}/test-connectivity`;
    
    try {
        const result = await this.apiRequest(url, 'POST');
        this.currentModal = 'test';
        this.modalTitle = `Test de connectivité: ${item.name}`;
        this.modalData = { type, item, results: result };
        this.showModal('testConnectivityModal');
    } catch (e) {
        console.error('Test connectivity error:', e);
    }
},

renderTestResults() {
    const results = this.modalData.results || {};
    const item = this.modalData.item || {};
    
    return `
        <div style="text-align: center; margin-bottom: 20px;">
            <h4 style="color: var(--primary-color); margin-bottom: 10px;">
                <i class="fas fa-network-wired"></i> Test de connectivité: ${item.name || 'Équipement'}
            </h4>
            <p style="color: var(--text-light);">Simulation des tests en cours...</p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f8fafc; border-radius: var(--border-radius); margin-bottom: 10px;">
                <div>
                    <strong>Ping IP NMS</strong>
                    <div style="font-size: 0.9rem; color: var(--text-light);">${item.ip_nms || 'N/A'}</div>
                </div>
                <span class="status-badge status-active" style="background: var(--success-color); color: white;">
                    <i class="fas fa-check"></i> Succès
                </span>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f8fafc; border-radius: var(--border-radius); margin-bottom: 10px;">
                <div>
                    <strong>Ping IP Service</strong>
                    <div style="font-size: 0.9rem; color: var(--text-light);">${item.ip_service || 'N/A'}</div>
                </div>
                <span class="status-badge status-active" style="background: var(--success-color); color: white;">
                    <i class="fas fa-check"></i> Succès
                </span>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f8fafc; border-radius: var(--border-radius); margin-bottom: 10px;">
                <div>
                    <strong>Test d'authentification</strong>
                    <div style="font-size: 0.9rem; color: var(--text-light);">Accès SSH/API</div>
                </div>
                <span class="status-badge status-active" style="background: var(--success-color); color: white;">
                    <i class="fas fa-check"></i> Succès
                </span>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: var(--text-light); font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> 
                Ces résultats sont simulés. En production, des tests réels seraient effectués.
            </p>
        </div>
    `;
},

// ------------------------------------------------------------
// Configuration spécifique
// ------------------------------------------------------------
configurePorts(switchId) {
    const item = this.switches.find(s => s.id === switchId);
    if (!item) return;
    this.currentModal = 'configurePorts';
    this.modalTitle = `Configuration des ports: ${item.name}`;
    this.modalData = { type: 'switch', item };
    this.formData = { portConfiguration: '' };
    this.showModal('configurePortsModal');
},

async savePortConfiguration() {
    const { item } = this.modalData;
    const url = `/api/switches/${item.id}/port-configuration`;
    try {
        await this.apiRequest(url, 'POST', { configuration: this.formData.portConfiguration });
        this.showToast('Configuration des ports mise à jour', 'success');
        this.closeModal('configurePortsModal');
    } catch (e) {
        console.error('Port configuration error:', e);
    }
},

updateInterfaces(routerId) {
    const item = this.routers.find(r => r.id === routerId);
    if (!item) return;
    this.currentModal = 'updateInterfaces';
    this.modalTitle = `Mise à jour des interfaces: ${item.name}`;
    this.modalData = { type: 'router', item };
    this.formData = { interfacesConfig: '' };
    this.showModal('updateInterfacesModal');
},

async saveInterfacesUpdate() {
    const { item } = this.modalData;
    const url = `/api/routers/${item.id}/update-interfaces`;
    try {
        await this.apiRequest(url, 'POST', { interfacesConfig: this.formData.interfacesConfig });
        this.showToast('Interfaces mises à jour', 'success');
        this.closeModal('updateInterfacesModal');
    } catch (e) {
        console.error('Interfaces update error:', e);
    }
},

updateSecurityPolicies(firewallId) {
    const item = this.firewalls.find(f => f.id === firewallId);
    if (!item) return;
    this.currentModal = 'updateSecurityPolicies';
    this.modalTitle = `Politiques de sécurité: ${item.name}`;
    this.modalData = { type: 'firewall', item };
    this.formData = { securityPolicies: '' };
    this.showModal('updateSecurityPoliciesModal');
},

async saveSecurityPolicies() {
    const { item } = this.modalData;
    const url = `/api/firewalls/${item.id}/update-security-policies`;
    try {
        await this.apiRequest(url, 'POST', { policies: this.formData.securityPolicies });
        this.showToast('Politiques de sécurité mises à jour', 'success');
        this.closeModal('updateSecurityPoliciesModal');
    } catch (e) {
        console.error('Security policies error:', e);
    }
},

// ------------------------------------------------------------
// Export
// ------------------------------------------------------------
exportDashboard() {
    const dataStr = JSON.stringify({
        sites: this.sites,
        switches: this.switches,
        routers: this.routers,
        firewalls: this.firewalls,
        totals: this.totals
    }, null, 2);
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `netconfig-export-${new Date().toISOString().slice(0,10)}.json`;
    a.click();
    this.showToast('Données exportées avec succès', 'success');
},

formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        // Il y a moins d'une minute
        if (diffMins < 1) {
            return 'À l\'instant';
        }
        // Il y a moins d'une heure
        else if (diffMins < 60) {
            return `Il y a ${diffMins} min`;
        }
        // Il y a moins de 24 heures
        else if (diffHours < 24) {
            return `Il y a ${diffHours}h`;
        }
        // Il y a moins de 7 jours
        else if (diffDays < 7) {
            return `Il y a ${diffDays}j`;
        }
        // Plus ancien
        else {
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    } catch (e) {
        console.error('Error formatting date:', e);
        return 'Date invalide';
    }
},

// ------------------------------------------------------------
// Icône de l'équipement
// ------------------------------------------------------------
getEquipmentIcon(type) {
    const icons = {
        'firewall': 'fa-fire',
        'router': 'fa-route',
        'switch': 'fa-exchange-alt',
        'site': 'fa-building'
    };
    return icons[type] || 'fa-server';
},

// ------------------------------------------------------------
// Afficher les détails de l'équipement dans un modal
// ------------------------------------------------------------
viewEquipmentDetails(type, item) {
    this.modalData = {
        type: type,
        item: item
    };
    this.showModal('equipmentDetailsModal');
},

// ------------------------------------------------------------
// Rendu HTML des détails de l'équipement
// ------------------------------------------------------------
getLastAccessUser(equipment) {
    // Si access_logs est chargé avec l'équipement
    if (equipment.access_logs && equipment.access_logs.length > 0) {
        const lastLog = equipment.access_logs[0]; // Le premier est le plus récent
        return lastLog.user?.name || lastLog.username || 'Inconnu';
    }
    
    // Si last_access_user est présent (ajouté par le contrôleur)
    if (equipment.last_access_user) {
        return equipment.last_access_user;
    }
    
    return 'Aucun accès';
},

// ------------------------------------------------------------
// Rendu HTML des détails de l'équipement (VERSION COMPLÈTE)
// ------------------------------------------------------------
renderEquipmentDetails() {
    const item = this.modalData.item;
    const type = this.modalData.type;
    
    if (!item) return '<p>Aucune donnée disponible</p>';
    
    let html = '<div style="display: grid; gap: 24px;">';
    
    // Section : Informations générales
    html += `
        <div class="detail-section" style="background: #f8fafc; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--primary-color); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle"></i> Informations générales
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Nom</div>
                    <div style="font-weight: 600; color: var(--text-color);">${item.name || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Site</div>
                    <div style="font-weight: 600; color: var(--text-color);">${item.site?.name || item.site || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Marque</div>
                    <div style="font-weight: 600; color: var(--text-color);">${item.brand || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Modèle</div>
                    <div style="font-weight: 600; color: var(--text-color);">${item.model || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Statut</div>
                    <div>
                        <span class="status-badge ${item.status ? 'status-active' : 'status-danger'}">
                            <i class="fas ${item.status ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                            ${item.status ? 'Actif' : 'Inactif'}
                        </span>
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">Numéro de série</div>
                    <div style="font-weight: 600; color: var(--text-color); font-family: monospace;">${item.serial_number || 'N/A'}</div>
                </div>
            </div>
        </div>
    `;
    
    // Section : Credentials d'accès ✅ NOUVEAU
    html += `
        <div class="detail-section" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--warning-color);">
            <h4 style="color: #92400e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-key"></i> Credentials d'accès
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 0.85rem; color: #92400e; margin-bottom: 4px; font-weight: 600;">
                        <i class="fas fa-user-shield"></i> Nom d'utilisateur
                    </div>
                    <div style="background: white; padding: 10px; border-radius: 8px; font-family: monospace; font-weight: 600; color: var(--text-color); display: flex; align-items: center; gap: 8px;">
                        ${item.username || '<span style="color: var(--text-light);">Non configuré</span>'}
                        ${item.username ? '<i class="fas fa-copy" style="cursor: pointer; color: var(--primary-color);" onclick="navigator.clipboard.writeText(\'' + item.username + '\'); alert(\'Copié!\')"></i>' : ''}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: #92400e; margin-bottom: 4px; font-weight: 600;">
                        <i class="fas fa-lock"></i> Mot de passe
                    </div>
                    <div style="background: white; padding: 10px; border-radius: 8px; font-family: monospace; font-weight: 600; color: var(--text-color);">
                        ${'•'.repeat(12)}
                        <span style="font-size: 0.75rem; color: var(--text-light); font-family: var(--font-secondary); margin-left: 8px;">(crypté)</span>
                    </div>
                </div>
                ${item.enable_password ? `
                <div>
                    <div style="font-size: 0.85rem; color: #92400e; margin-bottom: 4px; font-weight: 600;">
                        <i class="fas fa-shield-alt"></i> Enable Password
                    </div>
                    <div style="background: white; padding: 10px; border-radius: 8px; font-family: monospace; font-weight: 600; color: var(--text-color);">
                        ${'•'.repeat(12)}
                        <span style="font-size: 0.75rem; color: var(--text-light); font-family: var(--font-secondary); margin-left: 8px;">(crypté)</span>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    // Section : Derniers accès ✅ NOUVEAU
    if (item.access_logs && item.access_logs.length > 0) {
        html += `
            <div class="detail-section" style="background: #e0f2fe; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--info-color);">
                <h4 style="color: #0369a1; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-history"></i> Derniers accès (${item.access_logs.slice(0, 5).length})
                </h4>
                <div style="display: grid; gap: 10px;">
        `;
        
        item.access_logs.slice(0, 5).forEach((log, index) => {
            const logDate = new Date(log.accessed_at || log.created_at);
            html += `
                <div style="background: white; padding: 12px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">
                            ${(log.user?.name || log.username || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--text-color);">${log.user?.name || log.username || 'Utilisateur inconnu'}</div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">
                                <i class="fas fa-desktop"></i> ${log.ip_address || 'IP inconnue'}
                                ${log.action ? `• <i class="fas fa-cog"></i> ${log.action}` : ''}
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; font-size: 0.8rem; color: var(--text-light);">
                        ${this.formatDate(log.accessed_at || log.created_at)}
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
                ${item.access_logs.length > 5 ? `<div style="text-align: center; margin-top: 12px; color: var(--text-light); font-size: 0.85rem;">... et ${item.access_logs.length - 5} accès supplémentaires</div>` : ''}
            </div>
        `;
    } else {
        html += `
            <div class="detail-section" style="background: #f8fafc; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--text-light);">
                <h4 style="color: var(--text-light); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-history"></i> Derniers accès
                </h4>
                <p style="color: var(--text-light); text-align: center; padding: 20px;">
                    <i class="fas fa-info-circle"></i> Aucun accès enregistré
                </p>
            </div>
        `;
    }
    
    // Section : Configuration réseau
    html += `
        <div class="detail-section" style="background: #f8fafc; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--accent-color);">
            <h4 style="color: var(--accent-color); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-network-wired"></i> Configuration réseau
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
    `;
    
    if (type === 'firewall' || type === 'router') {
        html += `
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">IP NMS</div>
                    <div style="font-weight: 600; color: var(--text-color); font-family: monospace;">${item.ip_nms || 'N/A'}</div>
                    ${item.vlan_nms ? `<div style="font-size: 0.8rem; color: var(--text-light);">VLAN ${item.vlan_nms}</div>` : ''}
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">IP Service</div>
                    <div style="font-weight: 600; color: var(--text-color); font-family: monospace;">${item.ip_service || 'N/A'}</div>
                    ${item.vlan_service ? `<div style="font-size: 0.8rem; color: var(--text-light);">VLAN ${item.vlan_service}</div>` : ''}
                </div>
        `;
    }
    
    if (type === 'router' && item.management_ip) {
        html += `
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 4px;">IP Management</div>
                    <div style="font-weight: 600; color: var(--text-color); font-family: monospace;">${item.management_ip}</div>
                </div>
        `;
    }
    
    html += `
            </div>
        </div>
    `;
    
    // Sections spécifiques (firewall, router, switch) - code existant...
    // [Gardez le reste du code de renderEquipmentDetails() que vous aviez]
    
    html += '</div>';
    return html;
},
// ------------------------------------------------------------
// Obtenir la date du dernier accès
// ------------------------------------------------------------
getLastAccessDate(equipment) {
    // Essayer d'abord avec la relation lastAccessLog chargée
    if (equipment.last_access_log?.accessed_at) {
        return equipment.last_access_log.accessed_at;
    }
    
    if (equipment.last_access_log?.created_at) {
        return equipment.last_access_log.created_at;
    }
    
    // Sinon avec l'accesseur last_accessed_at
    if (equipment.last_accessed_at) {
        return equipment.last_accessed_at;
    }
    
    // Sinon chercher dans les access_logs si disponibles
    if (equipment.access_logs && equipment.access_logs.length > 0) {
        const lastLog = equipment.access_logs[0];
        return lastLog.accessed_at || lastLog.created_at;
    }
    
    // Par défaut : utiliser updated_at
    return equipment.updated_at;
},

        };
    }
</script>
</body>
</html>