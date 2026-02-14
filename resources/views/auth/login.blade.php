<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetConfig Pro - Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Reset et styles généraux */
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
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            font-family: var(--font-secondary);
        }

        /* En-tête */
        .main-header {
            background: linear-gradient(135deg, var(--header-bg) 0%, var(--header-dark) 100%);
            padding: 20px 0;
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
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 24px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .main-title {
            color: white;
            margin: 0;
            font-size: 1.8rem;
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

        /* Contenu principal */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        /* Carte de connexion */
        .auth-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 40px;
            box-shadow: var(--card-shadow-hover);
            width: 100%;
            max-width: 440px;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .auth-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
        }

        .auth-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .auth-title {
            color: var(--header-bg);
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* Formulaire */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 0.9em;
        }

        .form-control {
            padding: 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: var(--font-secondary);
            font-size: 1rem;
            color: var(--text-color);
            transition: var(--transition);
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .input-with-icon input {
            padding-left: 44px;
        }

        /* Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -5px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .checkbox-container input[type="checkbox"]:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkbox-label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
        }

        .forgot-password {
            font-size: 0.9rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 1rem;
            font-family: var(--font-secondary);
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0369a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
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

        /* Lien d'inscription */
        .auth-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .auth-footer p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            transition: var(--transition);
        }

        .auth-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Messages d'erreur */
        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid transparent;
            font-size: 0.9rem;
        }

        .alert-danger {
            color: #991b1b;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border-color: #f87171;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger i {
            font-size: 1.2em;
        }

        /* Pied de page */
        .main-footer {
            background: var(--header-bg);
            color: white;
            padding: 20px 0;
            text-align: center;
            font-size: 0.9rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }

        .footer-link {
            color: #cbd5e1;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-link:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-card {
                padding: 30px 24px;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .main-header {
                padding: 16px 0;
            }
            
            .main-title {
                font-size: 1.5rem;
            }
            
            .header-logo {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 24px 20px;
            }
            
            .auth-icon {
                width: 60px;
                height: 60px;
            }
            
            .auth-icon i {
                font-size: 2rem;
            }
            
            .main-content {
                padding: 24px 16px;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête principal -->
    <header class="main-header">
        <div class="header-content">
            <div class="header-brand">
                <img src="https://img.icons8.com/color/96/000000/network.png" alt="Logo NetConfig Pro" class="header-logo">
                <h1 class="main-title">
                    <i class="fas fa-network-wired"></i> NetConfig Pro
                </h1>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="auth-card">
            <!-- Messages d'erreur -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; border-color: #10b981;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h2 class="auth-title">Connexion à votre compte</h2>
                <p class="auth-subtitle">Accédez à la plateforme de gestion réseau</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i> Adresse email
                    </label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="form-control" 
                               placeholder="votre@email.com" 
                               required 
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="••••••••" 
                               required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkbox-label">Se souvenir de moi</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="auth-footer">
                <p>Nouveau sur NetConfig Pro ?</p>
                <a href="{{ route('register') }}" class="auth-link">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} NetConfig Pro - Plateforme de Gestion Réseau</p>
            <div class="footer-links">
                <a href="#" class="footer-link">Mentions légales</a>
                <a href="#" class="footer-link">Politique de confidentialité</a>
                <a href="#" class="footer-link">Support</a>
            </div>
        </div>
    </footer>

    <script>
        // Animation pour le bouton de soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion en cours...';
            submitBtn.disabled = true;
        });

        // Affichage/masquage du mot de passe
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.createElement('span');
        passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
        passwordToggle.style.cssText = `
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
        `;
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        document.querySelector('#password').parentNode.appendChild(passwordToggle);
    </script>
</body>
</html>