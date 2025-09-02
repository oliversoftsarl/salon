<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - 403 Forbidden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            margin: 20px;
            animation: fadeIn 0.6s ease-out;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #7f8c8d;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .security-note {
            margin-top: 25px;
            padding: 15px;
            background: #fff8e1;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
            font-size: 14px;
            color: #856404;
            text-align: left;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        
        @media (max-width: 480px) {
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .container {
                padding: 30px 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Accès Refusé</h1>
        
        <p class="error-message">
            Oups ! Il semble que vous n'ayez pas l'autorisation nécessaire 
            pour accéder à cette page. Cette zone est restreinte aux utilisateurs 
            ayant les privilèges appropriés.
        </p>
        
        <div class="action-buttons">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <span>🏠</span> Retour à l'accueil
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <span>↩️</span> Retour arrière
            </a>
            <button onclick="location.reload()" class="btn btn-secondary">
                <span>🔄</span> Réessayer
            </button>
        </div>
        
        <div class="security-note">
            <strong>💡 Information de sécurité :</strong><br>
            Si vous pensez que c'est une erreur, veuillez contacter 
            l'administrateur système ou votre responsable.
        </div>
    </div>

    <script>
        // Animation supplémentaire pour les boutons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translateY(0)';
            });
        });
        
        // Compteur de tentatives (optionnel)
        let attemptCount = 0;
        document.addEventListener('click', function() {
            attemptCount++;
            if (attemptCount >= 3) {
                const note = document.querySelector('.security-note');
                note.innerHTML += '<br><br>⚠️ <strong>Attention :</strong> Accès multiple tenté.';
            }
        });
    </script>
</body>
</html>