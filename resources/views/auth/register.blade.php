<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetConfig Pro - Inscription</title>
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

        /* Carte d'inscription */
        .auth-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 40px;
            box-shadow: var(--card-shadow-hover);
            width: 100%;
            max-width: 500px;
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
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
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

        /* Grille du formulaire */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .required::after {
            content: " *";
            color: var(--danger-color);
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

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 20px;
            padding-right: 44px;
        }

        /* Informations sur les rôles */
        .role-info {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #bae6fd;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 5px;
            font-size: 0.85rem;
            color: var(--text-color);
        }

        .role-info h4 {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-info ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .role-info li {
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-info li i {
            color: var(--success-color);
            font-size: 0.8em;
        }

        /* Force du mot de passe */
        .password-strength {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
        }

        .strength-meter {
            flex: 1;
            height: 6px;
            background: var(--border-color);
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            background: var(--danger-color);
            border-radius: 3px;
            transition: var(--transition);
        }

        .strength-fill.weak { width: 33%; background: var(--danger-color); }
        .strength-fill.medium { width: 66%; background: var(--warning-color); }
        .strength-fill.strong { width: 100%; background: var(--success-color); }

        .strength-text {
            font-weight: 600;
            min-width: 60px;
            text-align: right;
        }

        /* Messages d'erreur */
        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .error-message i {
            font-size: 0.9em;
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

        /* Lien de connexion */
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

        /* Conditions générales */
        .terms-check {
            margin-top: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }

        .terms-check label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .terms-check input[type="checkbox"] {
            margin-top: 4px;
            min-width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .terms-check input[type="checkbox"]:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .terms-text {
            font-size: 0.9rem;
            color: var(--text-color);
            line-height: 1.5;
        }

        .terms-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .terms-text a:hover {
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
                    <span>Veuillez corriger les erreurs ci-dessous.</span>
                </div>
            @endif

            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="auth-title">Créer votre compte</h2>
                <p class="auth-subtitle">Rejoignez la plateforme de gestion réseau</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required" for="name">
                            <i class="fas fa-user"></i> Nom complet
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               class="form-control" 
                               placeholder="John Doe" 
                               required>
                        @error('name')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="email">
                            <i class="fas fa-envelope"></i> Adresse email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="form-control" 
                               placeholder="votre@email.com" 
                               required>
                        @error('email')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="password">
                            <i class="fas fa-lock"></i> Mot de passe
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="••••••••" 
                               required>
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <div class="strength-text" id="strength-text">Faible</div>
                        </div>
                        @error('password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="password_confirmation">
                            <i class="fas fa-lock"></i> Confirmer le mot de passe
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="••••••••" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="role">
                            <i class="fas fa-user-tag"></i> Rôle
                        </label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="" disabled selected>Sélectionnez votre rôle</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                            <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technicien</option>
                            <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>Observateur</option>
                        </select>
                        <div class="role-info">
                            <h4><i class="fas fa-info-circle"></i> Description des rôles</h4>
                            <ul>
                                <li><i class="fas fa-check"></i> <strong>Administrateur:</strong> Accès complet</li>
                                <li><i class="fas fa-check"></i> <strong>Technicien:</strong> Édition limitée</li>
                                <li><i class="fas fa-check"></i> <strong>Observateur:</strong> Consultation seule</li>
                            </ul>
                        </div>
                        @error('role')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="department">
                            <i class="fas fa-building"></i> Département
                        </label>
                        <select id="department" name="department" class="form-control">
                            <option value="" selected>Sélectionnez un département</option>
                            <option value="IT" {{ old('department') == 'IT' ? 'selected' : '' }}>IT</option>
                            <option value="Network" {{ old('department') == 'Network' ? 'selected' : '' }}>Réseau</option>
                            <option value="Security" {{ old('department') == 'Security' ? 'selected' : '' }}>Sécurité</option>
                        </select>
                        @error('department')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone"></i> Téléphone
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               class="form-control" 
                               placeholder="+33 1 23 45 67 89">
                        @error('phone')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="terms-check">
                    <label>
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="terms-text">
                            J'accepte les <a href="#">conditions d'utilisation</a> et la 
                            <a href="#">politique de confidentialité</a> de NetConfig Pro.
                        </span>
                    </label>
                    @error('terms')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ?</p>
                <a href="{{ route('login') }}" class="auth-link">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
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
        // Validation de la force du mot de passe
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Longueur
            if (password.length >= 8) strength++;
            // Chiffres
            if (/\d/.test(password)) strength++;
            // Lettres minuscules et majuscules
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            // Caractères spéciaux
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Mettre à jour l'affichage
            strengthFill.className = 'strength-fill';
            if (strength === 0) {
                strengthText.textContent = '';
                strengthText.style.color = 'var(--text-light)';
            } else if (strength === 1) {
                strengthFill.classList.add('weak');
                strengthText.textContent = 'Faible';
                strengthText.style.color = 'var(--danger-color)';
            } else if (strength === 2) {
                strengthFill.classList.add('medium');
                strengthText.textContent = 'Moyen';
                strengthText.style.color = 'var(--warning-color)';
            } else if (strength >= 3) {
                strengthFill.classList.add('strong');
                strengthText.textContent = 'Fort';
                strengthText.style.color = 'var(--success-color)';
            }
        });

        // Affichage/masquage du mot de passe
        function setupPasswordToggle(inputId) {
            const input = document.getElementById(inputId);
            const toggle = document.createElement('span');
            toggle.innerHTML = '<i class="fas fa-eye"></i>';
            toggle.style.cssText = `
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-light);
                cursor: pointer;
            `;
            toggle.addEventListener('click', function() {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
            
            // Ajouter le bouton de bascule
            input.parentNode.style.position = 'relative';
            input.parentNode.appendChild(toggle);
        }

        setupPasswordToggle('password');
        setupPasswordToggle('password_confirmation');

        // Animation pour le bouton de soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création du compte...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>