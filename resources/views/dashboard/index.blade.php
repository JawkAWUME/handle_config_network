<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetConfig Pro - Gestion des Configurations Réseau</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        h1, h2, h3, h4, h5, h6 {
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
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Spinner de chargement de l'onglet */
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .py-5 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        /* Alertes */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
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
            .welcome-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <div class="breadcrumb">
        <a href="#"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a> &gt;
        <strong><i class="fas fa-network-wired"></i> Gestion des Configurations Réseau</strong>
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
                <button class="btn btn-accent" onclick="showCreateModal('site')">
                    <i class="fas fa-plus"></i> Nouveau Site
                </button>
                <button class="btn btn-outline" onclick="exportDashboard()">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>
    </header>

    <!-- Navigation par onglets -->
    <div class="tabs-navigation">
        <div class="tabs-container">
            <button class="tab-button active" data-tab="dashboard" onclick="loadTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Tableau de Bord
            </button>
            <button class="tab-button" data-tab="sites" onclick="loadTab('sites')">
                <i class="fas fa-building"></i> Sites
            </button>
            <button class="tab-button" data-tab="switches" onclick="loadTab('switches')">
                <i class="fas fa-exchange-alt"></i> Switchs
            </button>
            <button class="tab-button" data-tab="routers" onclick="loadTab('routers')">
                <i class="fas fa-route"></i> Routeurs
            </button>
            <button class="tab-button" data-tab="firewalls" onclick="loadTab('firewalls')">
                <i class="fas fa-fire"></i> Firewalls
            </button>
        </div>
    </div>

    <!-- Contenu principal (chargé dynamiquement) -->
    <div class="dashboard-container" id="tab-content">
        <!-- Contenu chargé dynamiquement via JavaScript -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3">Chargement des données...</p>
        </div>
    </div>

    <script>
        // Variables globales
        let currentTab = 'dashboard';
        let isLoading = false;
        let charts = {};
        let chartTypes = {
            deviceDistributionChart: 'pie',
            availabilityChart: 'line',
            incidentsChart: 'bar',
            loadChart: 'line'
        };

        // Données de test (simulées - à remplacer par des appels API réels)
        const testData = {
            totals: {
                devices: 184,
                availability: 99.7,
                avgUptime: 45,
                incidentsToday: 3,
                sites: 8,
                firewalls: 42,
                routers: 56,
                switches: 86
            },
            kpis: [
                { icon: 'fa-shield-alt', value: '99.5%', label: 'Sécurité', trend: 'up', trendValue: '+0.3%' },
                { icon: 'fa-bolt', value: '98.2%', label: 'Performance', trend: 'stable', trendValue: '±0.1%' },
                { icon: 'fa-hdd', value: '42', label: 'Backups Aujourd\'hui', trend: 'up', trendValue: '+5' },
                { icon: 'fa-exclamation-circle', value: '12', label: 'Alertes Actives', trend: 'down', trendValue: '-3' },
                { icon: 'fa-clock', value: '2.4s', label: 'Latence Moyenne', trend: 'down', trendValue: '-0.3s' },
                { icon: 'fa-database', value: '1.2TB', label: 'Trafic Quotidien', trend: 'up', trendValue: '+120GB' }
            ],
            deviceDistribution: {
                labels: ['Firewalls', 'Routeurs', 'Switchs', 'Load Balancers', 'Serveurs'],
                data: [42, 56, 86, 12, 28],
                colors: ['#0ea5e9', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444']
            },
            availabilityData: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                data: [99.2, 99.5, 99.8, 99.7, 99.6, 99.9, 99.4]
            },
            incidentsData: {
                labels: ['Connexion', 'CPU', 'Mémoire', 'Bande Passante', 'Disque'],
                data: [12, 8, 5, 15, 3]
            },
            loadData: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                firewalls: [45, 48, 62, 68, 55, 50],
                routers: [60, 58, 72, 78, 65, 62],
                switches: [40, 42, 55, 58, 48, 45]
            },
            sites: [
                { id: 1, name: 'Siège Social', address: '123 Avenue des Champs', city: 'Paris', devices: 42, status: 'active', lastUpdate: '2024-01-15' },
                { id: 2, name: 'Centre Data', address: '456 Rue du Serveur', city: 'Lyon', devices: 128, status: 'active', lastUpdate: '2024-01-14' },
                { id: 3, name: 'Agence Nord', address: '789 Boulevard du Nord', city: 'Lille', devices: 28, status: 'active', lastUpdate: '2024-01-13' },
                { id: 4, name: 'Agence Sud', address: '101 Rue du Soleil', city: 'Marseille', devices: 35, status: 'warning', lastUpdate: '2024-01-12' },
                { id: 5, name: 'Backup Center', address: '202 Avenue de la Sécurité', city: 'Bordeaux', devices: 56, status: 'active', lastUpdate: '2024-01-11' },
                { id: 6, name: 'Dev Lab', address: '303 Rue du Code', city: 'Toulouse', devices: 18, status: 'inactive', lastUpdate: '2024-01-10' },
                { id: 7, name: 'QA Center', address: '404 Boulevard du Test', city: 'Nice', devices: 24, status: 'active', lastUpdate: '2024-01-09' },
                { id: 8, name: 'DR Site', address: '505 Rue de la Redondance', city: 'Strasbourg', devices: 32, status: 'active', lastUpdate: '2024-01-08' }
            ],
            firewalls: [
                { id: 1, name: 'FW-PARIS-01', site: 'Siège Social', model: 'Fortinet 600F', ip_nms: '192.168.1.1', ip_service: '192.168.1.2', status: true, security_policies_count: 128, cpu: 45, memory: 65, lastSeen: 'Il y a 5 min' },
                { id: 2, name: 'FW-LYON-CORE', site: 'Centre Data', model: 'Palo Alto PA-3220', ip_nms: '10.0.1.1', ip_service: '10.0.1.2', status: false, security_policies_count: 256, cpu: 78, memory: 82, lastSeen: 'Il y a 12 min' },
                { id: 3, name: 'FW-MARSEILLE-01', site: 'Agence Sud', model: 'Fortinet 400F', ip_nms: '172.16.1.1', ip_service: '172.16.1.2', status: true, security_policies_count: 96, cpu: 32, memory: 45, lastSeen: 'Il y a 3 min' },
                { id: 4, name: 'FW-BORDEAUX-BKP', site: 'Backup Center', model: 'Cisco ASA 5525', ip_nms: '192.168.2.1', ip_service: '192.168.2.2', status: true, security_policies_count: 64, cpu: 25, memory: 38, lastSeen: 'Il y a 8 min' },
                { id: 5, name: 'FW-LILLE-01', site: 'Agence Nord', model: 'Fortinet 200F', ip_nms: '10.10.1.1', ip_service: '10.10.1.2', status: true, security_policies_count: 48, cpu: 55, memory: 60, lastSeen: 'Il y a 2 min' }
            ],
            routers: [
                { id: 1, name: 'RT-PARIS-CORE', site: 'Siège Social', brand: 'Cisco', model: 'ASR 1001-X', management_ip: '192.168.1.254', interfaces_up_count: 24, interfaces_count: 24, status: true, lastSeen: 'Il y a 4 min' },
                { id: 2, name: 'RT-LYON-CORE', site: 'Centre Data', brand: 'Juniper', model: 'MX204', management_ip: '10.0.1.254', interfaces_up_count: 22, interfaces_count: 24, status: false, lastSeen: 'Il y a 15 min' },
                { id: 3, name: 'RT-MARSEILLE-01', site: 'Agence Sud', brand: 'Cisco', model: 'ISR 4451', management_ip: '172.16.1.254', interfaces_up_count: 16, interfaces_count: 16, status: true, lastSeen: 'Il y a 2 min' },
                { id: 4, name: 'RT-BORDEAUX-01', site: 'Backup Center', brand: 'Mikrotik', model: 'CCR1072', management_ip: '192.168.2.254', interfaces_up_count: 12, interfaces_count: 12, status: true, lastSeen: 'Il y a 7 min' },
                { id: 5, name: 'RT-LILLE-EDGE', site: 'Agence Nord', brand: 'Cisco', model: 'ISR 4321', management_ip: '10.10.1.254', interfaces_up_count: 8, interfaces_count: 8, status: true, lastSeen: 'Il y a 1 min' }
            ],
            switches: [
                { id: 1, name: 'SW-PARIS-CORE-01', site: 'Siège Social', model: 'Cisco Catalyst 9300', ports: '48', vlans: 24, status: 'active', lastSeen: 'Il y a 3 min' },
                { id: 2, name: 'SW-LYON-CORE-01', site: 'Centre Data', model: 'Aruba 8325', ports: '48', vlans: 32, status: 'active', lastSeen: 'Il y a 10 min' },
                { id: 3, name: 'SW-MARSEILLE-ACCESS', site: 'Agence Sud', model: 'Cisco Catalyst 9200', ports: '24', vlans: 12, status: 'warning', lastSeen: 'Il y a 20 min' },
                { id: 4, name: 'SW-BORDEAUX-CORE', site: 'Backup Center', model: 'Juniper EX4300', ports: '48', vlans: 20, status: 'active', lastSeen: 'Il y a 6 min' },
                { id: 5, name: 'SW-LILLE-ACCESS', site: 'Agence Nord', model: 'HP Aruba 2930F', ports: '24', vlans: 10, status: 'active', lastSeen: 'Il y a 2 min' }
            ]
        };

        // Charger le tableau de bord au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadTab('dashboard');
        });

        // Charger un onglet
        async function loadTab(tabName) {
            if (isLoading) return;
            
            isLoading = true;
            currentTab = tabName;
            
            // Mettre à jour l'onglet actif
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`.tab-button[data-tab="${tabName}"]`).classList.add('active');
            
            // Afficher le loader
            const tabContent = document.getElementById('tab-content');
            tabContent.innerHTML = `
                <div id="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3">Chargement des données...</p>
                </div>
            `;
            
            try {
                // Charger le contenu de l'onglet
                let htmlContent = '';
                
                switch(tabName) {
                    case 'dashboard':
                        htmlContent = await loadDashboardContent();
                        break;
                    case 'sites':
                        htmlContent = await loadSitesContent();
                        break;
                    case 'switches':
                        htmlContent = await loadSwitchesContent();
                        break;
                    case 'routers':
                        htmlContent = await loadRoutersContent();
                        break;
                    case 'firewalls':
                        htmlContent = await loadFirewallsContent();
                        break;
                    default:
                        htmlContent = '<div class="alert alert-danger">Onglet non trouvé</div>';
                }
                
                // Afficher le contenu
                tabContent.innerHTML = htmlContent;
                
                // Initialiser les événements pour le nouvel onglet
                initializeTabEvents();
                
            } catch (error) {
                console.error('Erreur lors du chargement de l\'onglet:', error);
                tabContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h4>Erreur de chargement</h4>
                        <p>${error.message}</p>
                        <button class="btn btn-primary mt-2" onclick="loadTab('${tabName}')">
                            <i class="fas fa-redo"></i> Réessayer
                        </button>
                    </div>
                `;
            } finally {
                isLoading = false;
            }
        }

        // Charger le contenu du dashboard
        async function loadDashboardContent() {
            // Simuler le chargement des données API
            await new Promise(resolve => setTimeout(resolve, 500));
            
            return `
                <section class="welcome-section fade-in">
                    <div class="welcome-content">
                        <div class="welcome-header">
                            <div class="welcome-text">
                                <h2 class="welcome-title">Bienvenue, Administrateur!</h2>
                                <p class="welcome-subtitle">
                                    Plateforme complète de gestion des configurations réseau pour simplifier l'exploitation, la maintenance et les audits techniques de votre infrastructure.
                                </p>
                                <div class="welcome-stats">
                                    <div class="welcome-stat">
                                        <div class="stat-value" id="total-sites">${testData.totals.sites}</div>
                                        <div class="stat-label">Sites Actifs</div>
                                    </div>
                                    <div class="welcome-stat">
                                        <div class="stat-value" id="total-devices">${testData.totals.devices}</div>
                                        <div class="stat-label">Équipements</div>
                                    </div>
                                    <div class="welcome-stat">
                                        <div class="stat-value" id="connectivity-rate">${testData.totals.availability}%</div>
                                        <div class="stat-label">Disponibilité</div>
                                    </div>
                                    <div class="welcome-stat">
                                        <div class="stat-value" id="incidents-today">${testData.totals.incidentsToday}</div>
                                        <div class="stat-label">Incidents</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="kpi-section fade-in">
                    <h2 style="color: var(--header-bg); margin-bottom: 24px; font-size: 1.8rem;">
                        <i class="fas fa-tachometer-alt"></i> Indicateurs Clés de Performance
                    </h2>
                    <div class="kpi-grid" id="kpi-grid">
                        <!-- Les KPI seront chargés dynamiquement par JavaScript -->
                    </div>
                </section>

                <section class="charts-section fade-in">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-pie"></i> Répartition des Équipements
                            </h3>
                            <button class="btn btn-outline btn-sm" onclick="toggleChartType('deviceDistribution')">
                                <i class="fas fa-exchange-alt"></i> Type
                            </button>
                        </div>
                        <div class="chart-container">
                            <canvas id="deviceDistributionChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-chart-line"></i> Disponibilité Hebdomadaire
                            </h3>
                            <button class="btn btn-outline btn-sm" onclick="toggleChartType('availabilityChart')">
                                <i class="fas fa-exchange-alt"></i> Type
                            </button>
                        </div>
                        <div class="chart-container">
                            <canvas id="availabilityChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-exclamation-triangle"></i> Incidents par Type
                            </h3>
                            <button class="btn btn-outline btn-sm" onclick="toggleChartType('incidentsChart')">
                                <i class="fas fa-exchange-alt"></i> Type
                            </button>
                        </div>
                        <div class="chart-container">
                            <canvas id="incidentsChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">
                                <i class="fas fa-server"></i> Charge des Équipements
                            </h3>
                            <button class="btn btn-outline btn-sm" onclick="toggleChartType('loadChart')">
                                <i class="fas fa-exchange-alt"></i> Type
                            </button>
                        </div>
                        <div class="chart-container">
                            <canvas id="loadChart"></canvas>
                        </div>
                    </div>
                </section>

                <section class="equipment-section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-history"></i> Activité Récente
                        </h2>
                        <div class="section-actions">
                            <button class="btn btn-outline" onclick="loadAllRecentActivity()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                                <i class="fas fa-fire"></i> Derniers Firewalls
                            </h4>
                            <div id="recent-firewalls" style="max-height: 300px; overflow-y: auto;">
                                <!-- Chargement dynamique -->
                            </div>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                                <i class="fas fa-route"></i> Derniers Routeurs
                            </h4>
                            <div id="recent-routers" style="max-height: 300px; overflow-y: auto;">
                                <!-- Chargement dynamique -->
                            </div>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 15px;">
                                <i class="fas fa-exchange-alt"></i> Derniers Switchs
                            </h4>
                            <div id="recent-switches" style="max-height: 300px; overflow-y: auto;">
                                <!-- Chargement dynamique -->
                            </div>
                        </div>
                    </div>
                </section>
            `;
        }

        // Charger le contenu des sites
        async function loadSitesContent() {
            await new Promise(resolve => setTimeout(resolve, 500));
            
            return `
                <div class="filters-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="sites-search" placeholder="Rechercher un site..." onkeyup="filterSites()">
                    </div>
                    <div class="filter-group">
                        <select class="filter-select" id="sites-status-filter" onchange="filterSites()">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="warning">Avertissement</option>
                            <option value="inactive">Inactif</option>
                        </select>
                        <select class="filter-select" id="sites-sort" onchange="sortSites()">
                            <option value="name">Trier par nom</option>
                            <option value="devices">Trier par équipements</option>
                            <option value="city">Trier par ville</option>
                        </select>
                    </div>
                </div>

                <section class="equipment-section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-building"></i> Gestion des Sites
                        </h2>
                        <div class="section-actions">
                            <span class="status-badge status-info">${testData.sites.length} sites</span>
                            <button class="btn btn-primary" onclick="showCreateModal('site')">
                                <i class="fas fa-plus"></i> Nouveau Site
                            </button>
                            <button class="btn btn-outline" onclick="exportSites()">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Adresse</th>
                                    <th>Ville</th>
                                    <th>Équipements</th>
                                    <th>Statut</th>
                                    <th>Dernière MAJ</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sites-table-body">
                                ${testData.sites.map(site => `
                                    <tr>
                                        <td>
                                            <strong>${site.name}</strong><br>
                                            <small class="text-muted">ID: ${site.id}</small>
                                        </td>
                                        <td>${site.address}</td>
                                        <td>${site.city}</td>
                                        <td>
                                            <span class="status-badge status-info">
                                                ${site.devices} équipements
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge ${site.status === 'active' ? 'status-active' : site.status === 'warning' ? 'status-warning' : 'status-danger'}">
                                                ${site.status === 'active' ? 'Actif' : site.status === 'warning' ? 'Avertissement' : 'Inactif'}
                                            </span>
                                        </td>
                                        <td>${site.lastUpdate}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-outline btn-sm btn-icon" title="Voir" onclick="viewSite(${site.id})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm btn-icon" title="Éditer" onclick="editSite(${site.id})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </section>
            `;
        }

        // Charger le contenu des switchs
        async function loadSwitchesContent() {
            await new Promise(resolve => setTimeout(resolve, 500));
            
            return `
                <div class="filters-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="switches-search" placeholder="Rechercher un switch..." onkeyup="filterSwitches()">
                    </div>
                    <div class="filter-group">
                        <select class="filter-select" id="switches-status-filter" onchange="filterSwitches()">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="warning">Avertissement</option>
                            <option value="danger">Critique</option>
                        </select>
                        <select class="filter-select" id="switches-site-filter" onchange="filterSwitches()">
                            <option value="">Tous les sites</option>
                            ${[...new Set(testData.switches.map(s => s.site))].map(site => `<option value="${site}">${site}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <section class="equipment-section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-exchange-alt"></i> Gestion des Switchs
                        </h2>
                        <div class="section-actions">
                            <span class="status-badge status-info">${testData.switches.length} switchs</span>
                            <button class="btn btn-primary" onclick="showCreateModal('switch')">
                                <i class="fas fa-plus"></i> Nouveau Switch
                            </button>
                            <button class="btn btn-outline" onclick="exportSwitches()">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Site</th>
                                    <th>Modèle</th>
                                    <th>Ports</th>
                                    <th>VLANs</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="switches-table-body">
                                ${testData.switches.map(sw => `
                                    <tr>
                                        <td>
                                            <strong>${sw.name}</strong><br>
                                            <small class="text-muted">${sw.model}</small>
                                        </td>
                                        <td>${sw.site}</td>
                                        <td>${sw.model}</td>
                                        <td>${sw.ports}</td>
                                        <td>
                                            <span class="status-badge status-info">
                                                ${sw.vlans} VLANs
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge ${sw.status === 'active' ? 'status-active' : sw.status === 'warning' ? 'status-warning' : 'status-danger'}">
                                                ${sw.status === 'active' ? 'Actif' : sw.status === 'warning' ? 'Avertissement' : 'Critique'}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-outline btn-sm btn-icon" title="Voir" onclick="viewSwitch(${sw.id})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm btn-icon" title="Configurer" onclick="configureSwitch(${sw.id})">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </section>
            `;
        }

        // Charger le contenu des routeurs
        async function loadRoutersContent() {
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Construire le tableau des routeurs
            let tableRows = '';
            if (testData.routers.length > 0) {
                tableRows = testData.routers.map(router => `
                    <tr>
                        <td>
                            <strong>${router.name}</strong><br>
                            <small class="text-muted">${router.model}</small>
                        </td>
                        <td>${router.site}</td>
                        <td>
                            <div><code>${router.management_ip}</code></div>
                            <small>${router.brand}</small>
                        </td>
                        <td>
                            <span class="status-badge status-info">
                                ${router.interfaces_up_count}/${router.interfaces_count}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge ${router.status ? 'status-active' : 'status-danger'}">
                                ${router.status ? 'Actif' : 'Inactif'}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-outline btn-sm btn-icon" title="Voir" onclick="viewRouter(${router.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline btn-sm btn-icon" title="Tester" onclick="testRouter(${router.id})">
                                    <i class="fas fa-plug"></i>
                                </button>
                                <button class="btn btn-outline btn-sm btn-icon" title="Configurer" onclick="configureRouter(${router.id})">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableRows = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="py-5">
                                <i class="fas fa-route fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun routeur trouvé</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
            
            return `
                <div class="filters-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="routers-search" placeholder="Rechercher un routeur..." onkeyup="filterRouters()">
                    </div>
                    <div class="filter-group">
                        <select class="filter-select" id="routers-status-filter" onchange="filterRouters()">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                        <select class="filter-select" id="routers-site-filter" onchange="filterRouters()">
                            <option value="">Tous les sites</option>
                            ${[...new Set(testData.routers.map(r => r.site))].map(site => `<option value="${site}">${site}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <section class="equipment-section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-route"></i> Gestion des Routeurs
                        </h2>
                        <div class="section-actions">
                            <span class="status-badge status-info">${testData.routers.length} routeurs</span>
                            <button class="btn btn-primary" onclick="showCreateModal('router')">
                                <i class="fas fa-plus"></i> Nouveau Routeur
                            </button>
                            <button class="btn btn-outline" onclick="exportRouters()">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Site</th>
                                    <th>IP Management / Marque</th>
                                    <th>Interfaces</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="routers-table-body">
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </section>
            `;
        }

        // Charger le contenu des firewalls
        async function loadFirewallsContent() {
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Construire le tableau des firewalls
            let tableRows = '';
            if (testData.firewalls.length > 0) {
                tableRows = testData.firewalls.map(firewall => `
                    <tr>
                        <td>
                            <strong>${firewall.name}</strong><br>
                            <small class="text-muted">${firewall.model}</small>
                        </td>
                        <td>${firewall.site}</td>
                        <td>
                            <div><code>${firewall.ip_nms}</code></div>
                            <small><code>${firewall.ip_service}</code></small>
                        </td>
                        <td>
                            <span class="status-badge ${firewall.status ? 'status-active' : 'status-danger'}">
                                ${firewall.status ? 'Actif' : 'Inactif'}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-info">
                                ${firewall.security_policies_count} règles
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-outline btn-sm btn-icon" title="Voir" onclick="viewFirewall(${firewall.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline btn-sm btn-icon" title="Tester" onclick="testFirewall(${firewall.id})">
                                    <i class="fas fa-plug"></i>
                                </button>
                                <button class="btn btn-outline btn-sm btn-icon" title="Configurer" onclick="configureFirewall(${firewall.id})">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableRows = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="py-5">
                                <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun firewall trouvé</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
            
            return `
                <div class="filters-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="firewalls-search" placeholder="Rechercher un firewall..." onkeyup="filterFirewalls()">
                    </div>
                    <div class="filter-group">
                        <select class="filter-select" id="firewalls-status-filter" onchange="filterFirewalls()">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                        <select class="filter-select" id="firewalls-site-filter" onchange="filterFirewalls()">
                            <option value="">Tous les sites</option>
                            ${[...new Set(testData.firewalls.map(f => f.site))].map(site => `<option value="${site}">${site}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <section class="equipment-section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-fire"></i> Gestion des Firewalls
                        </h2>
                        <div class="section-actions">
                            <span class="status-badge status-info">${testData.firewalls.length} firewalls</span>
                            <button class="btn btn-primary" onclick="showCreateModal('firewall')">
                                <i class="fas fa-plus"></i> Nouveau Firewall
                            </button>
                            <button class="btn btn-outline" onclick="exportFirewalls()">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Site</th>
                                    <th>IP NMS / SERVICE</th>
                                    <th>Statut</th>
                                    <th>Politiques</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="firewalls-table-body">
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </section>
            `;
        }

        // Fonctions utilitaires
        async function fetchData(url) {
            // Simulation d'appel API
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Pour la démo, retourner les données de test
            return {
                success: true,
                data: testData
            };
        }

        function initializeTabEvents() {
            // Initialiser les événements spécifiques à l'onglet
            if (currentTab === 'dashboard') {
                loadDashboardKpis();
                initializeCharts();
                loadRecentActivity();
            }
        }

        // Fonctions pour le dashboard
        async function loadDashboardKpis() {
            try {
                const kpiGrid = document.getElementById('kpi-grid');
                if (!kpiGrid) return;
                
                // Construire les KPI
                kpiGrid.innerHTML = `
                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="kpi-value">${testData.totals.sites}</div>
                        <div class="kpi-label">Sites réseau</div>
                        <div class="kpi-trend trend-up">
                            <i class="fas fa-arrow-up"></i> Configuration centralisée
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="kpi-value">${testData.totals.firewalls}</div>
                        <div class="kpi-label">Firewalls</div>
                        <div class="kpi-trend trend-up">
                            <i class="fas fa-arrow-up"></i> 
                            ${Math.round((testData.firewalls.filter(f => f.status).length / testData.firewalls.length) * 100)}% actifs
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="kpi-value">${testData.totals.routers}</div>
                        <div class="kpi-label">Routeurs</div>
                        <div class="kpi-trend trend-up">
                            <i class="fas fa-arrow-up"></i> 
                            ${Math.round((testData.routers.filter(r => r.status).length / testData.routers.length) * 100)}% actifs
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="kpi-value">${testData.totals.switches}</div>
                        <div class="kpi-label">Switchs</div>
                        <div class="kpi-trend">
                            <i class="fas fa-minus"></i> Gestion VLAN
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-save"></i>
                        </div>
                        <div class="kpi-value">12</div>
                        <div class="kpi-label">Backups nécessaires</div>
                        <div class="kpi-trend trend-down">
                            <i class="fas fa-arrow-down"></i> À planifier
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="kpi-value">${testData.totals.availability}%</div>
                        <div class="kpi-label">Disponibilité</div>
                        <div class="kpi-trend ${testData.totals.availability > 95 ? 'trend-up' : (testData.totals.availability > 80 ? '' : 'trend-down')}">
                            <i class="fas fa-${testData.totals.availability > 95 ? 'arrow-up' : (testData.totals.availability > 80 ? 'minus' : 'arrow-down')}"></i> 
                            Taux de connectivité global
                        </div>
                    </div>
                `;
                
            } catch (error) {
                console.error('Erreur lors du chargement des KPI:', error);
            }
        }

        // Initialiser les graphiques
        function initializeCharts() {
            // Graphique de répartition des équipements
            const deviceCtx = document.getElementById('deviceDistributionChart')?.getContext('2d');
            if (deviceCtx) {
                charts.deviceDistribution = new Chart(deviceCtx, {
                    type: 'pie',
                    data: {
                        labels: testData.deviceDistribution.labels,
                        datasets: [{
                            data: testData.deviceDistribution.data,
                            backgroundColor: testData.deviceDistribution.colors,
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
                            }
                        }
                    }
                });
            }

            // Graphique de disponibilité
            const availabilityCtx = document.getElementById('availabilityChart')?.getContext('2d');
            if (availabilityCtx) {
                charts.availability = new Chart(availabilityCtx, {
                    type: 'line',
                    data: {
                        labels: testData.availabilityData.labels,
                        datasets: [{
                            label: 'Disponibilité (%)',
                            data: testData.availabilityData.data,
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
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

            // Graphique des incidents
            const incidentsCtx = document.getElementById('incidentsChart')?.getContext('2d');
            if (incidentsCtx) {
                charts.incidents = new Chart(incidentsCtx, {
                    type: 'bar',
                    data: {
                        labels: testData.incidentsData.labels,
                        datasets: [{
                            label: 'Nombre d\'Incidents',
                            data: testData.incidentsData.data,
                            backgroundColor: [
                                '#ef4444',
                                '#f59e0b',
                                '#0ea5e9',
                                '#10b981',
                                '#8b5cf6'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Graphique de charge
            const loadCtx = document.getElementById('loadChart')?.getContext('2d');
            if (loadCtx) {
                charts.load = new Chart(loadCtx, {
                    type: 'line',
                    data: {
                        labels: testData.loadData.labels,
                        datasets: [
                            {
                                label: 'Firewalls',
                                data: testData.loadData.firewalls,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                borderWidth: 2,
                                tension: 0.4
                            },
                            {
                                label: 'Routeurs',
                                data: testData.loadData.routers,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                tension: 0.4
                            },
                            {
                                label: 'Switchs',
                                data: testData.loadData.switches,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'rgba(14, 165, 233, 0.1)',
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
        }

        // Charger l'activité récente
        function loadRecentActivity() {
            // Firewalls récents
            const recentFirewalls = document.getElementById('recent-firewalls');
            if (recentFirewalls) {
                recentFirewalls.innerHTML = testData.firewalls.map(fw => {
                    const statusClass = fw.status ? 'status-active' : 'status-danger';
                    return `
                        <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${fw.name}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-light);">
                                        ${fw.site} • ${fw.model}
                                    </div>
                                </div>
                                <span class="status-badge ${statusClass}" style="font-size: 0.7rem;">
                                    ${fw.status ? 'Actif' : 'Inactif'}
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                <i class="fas fa-clock"></i> ${fw.lastSeen}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Routeurs récents
            const recentRouters = document.getElementById('recent-routers');
            if (recentRouters) {
                recentRouters.innerHTML = testData.routers.map(rt => {
                    const statusClass = rt.status ? 'status-active' : 'status-danger';
                    return `
                        <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${rt.name}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-light);">
                                        ${rt.site} • ${rt.model}
                                    </div>
                                </div>
                                <span class="status-badge ${statusClass}" style="font-size: 0.7rem;">
                                    ${rt.status ? 'Actif' : 'Inactif'}
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                <i class="fas fa-clock"></i> ${rt.lastSeen}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Switchs récents
            const recentSwitches = document.getElementById('recent-switches');
            if (recentSwitches) {
                recentSwitches.innerHTML = testData.switches.map(sw => {
                    const statusClass = sw.status === 'active' ? 'status-active' : sw.status === 'warning' ? 'status-warning' : 'status-danger';
                    return `
                        <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${sw.name}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-light);">
                                        ${sw.site} • ${sw.model}
                                    </div>
                                </div>
                                <span class="status-badge ${statusClass}" style="font-size: 0.7rem;">
                                    ${sw.status === 'active' ? 'Actif' : sw.status === 'warning' ? 'Avertissement' : 'Critique'}
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;">
                                <i class="fas fa-clock"></i> ${sw.lastSeen}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Fonctions utilitaires pour les actions
        function viewFirewall(id) {
            alert(`Voir les détails du firewall ${id}`);
        }

        function testFirewall(id) {
            alert(`Test de connectivité du firewall ${id} - Simulation en cours...`);
        }

        function configureFirewall(id) {
            alert(`Configuration du firewall ${id}`);
        }

        function viewRouter(id) {
            alert(`Voir les détails du routeur ${id}`);
        }

        function testRouter(id) {
            alert(`Test de connectivité du routeur ${id} - Simulation en cours...`);
        }

        function configureRouter(id) {
            alert(`Configuration du routeur ${id}`);
        }

        function viewSwitch(id) {
            alert(`Voir les détails du switch ${id}`);
        }

        function configureSwitch(id) {
            alert(`Configuration du switch ${id}`);
        }

        function viewSite(id) {
            alert(`Voir les détails du site ${id}`);
        }

        function editSite(id) {
            alert(`Éditer le site ${id}`);
        }

        // Fonctions de filtrage (simplifiées pour la démo)
        function filterSites() {
            console.log('Filtrage des sites');
        }

        function filterFirewalls() {
            console.log('Filtrage des firewalls');
        }

        function filterRouters() {
            console.log('Filtrage des routeurs');
        }

        function filterSwitches() {
            console.log('Filtrage des switchs');
        }

        function sortSites() {
            console.log('Tri des sites');
        }

        // Changer le type de graphique
        function toggleChartType(chartId) {
            const chartName = chartId.replace('Chart', '');
            const chart = charts[chartName];
            
            if (chart) {
                const currentType = chart.config.type;
                const newType = currentType === 'pie' ? 'bar' : 
                              currentType === 'bar' ? 'line' : 
                              currentType === 'line' ? 'pie' : 'bar';
                
                chart.config.type = newType;
                chart.update();
                
                // Mettre à jour le bouton
                const btn = event.target.closest('button');
                const icon = btn.querySelector('i');
                icon.className = newType === 'pie' ? 'fas fa-chart-pie' :
                                newType === 'bar' ? 'fas fa-chart-bar' :
                                'fas fa-chart-line';
            }
        }

        // Charger tous les équipements récents
        function loadAllRecentActivity() {
            loadRecentActivity();
            showNotification('Activité récente actualisée', 'success');
        }

        // Exporter les données
        function exportDashboard() {
            alert('Export du dashboard en cours...');
        }

        function exportFirewalls() {
            alert('Export des firewalls en cours...');
        }

        function exportRouters() {
            alert('Export des routeurs en cours...');
        }

        function exportSwitches() {
            alert('Export des switchs en cours...');
        }

        function exportSites() {
            alert('Export des sites en cours...');
        }

        // Afficher une notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                border-radius: var(--border-radius);
                box-shadow: var(--card-shadow);
                z-index: 1000;
                animation: fadeIn 0.3s ease;
                font-weight: 600;
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'fadeIn 0.3s ease reverse';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Simulation de données en temps réel
        setInterval(() => {
            if (currentTab === 'dashboard') {
                // Mettre à jour un KPI aléatoire
                const randomIndex = Math.floor(Math.random() * testData.kpis.length);
                const kpi = testData.kpis[randomIndex];
                
                if (kpi.label.includes('%')) {
                    const currentValue = parseFloat(kpi.value);
                    const change = (Math.random() - 0.5) * 0.2;
                    kpi.value = (currentValue + change).toFixed(1) + '%';
                    kpi.trend = change > 0 ? 'up' : change < 0 ? 'down' : 'stable';
                    kpi.trendValue = `${change > 0 ? '+' : ''}${change.toFixed(1)}%`;
                }
                
                // Recharger les KPI
                loadDashboardKpis();
            }
        }, 15000); // Toutes les 15 secondes
    </script>
</body>
</html>