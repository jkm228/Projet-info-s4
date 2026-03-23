<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Africa United - Espace Administrateur</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <header class="admin-topbar">
        <div class="header-title">
            <span class="admin-brand">AFRICA UNITED - BACK-OFFICE</span>
        </div>
        <div class="auth-buttons">
            <a href="accueil.html" class="login btn-exit-admin">Quitter l'admin</a>
        </div>
    </header>

    <main class="admin-page admin-compact">
        <div class="admin-header compact-header">
            <h1>Gestion de la Clientèle</h1>
            <p>Suivi des utilisateurs inscrits et de leur activité.</p>
        </div>

        <div class="admin-container">
            
            <div class="dashboard-card admin-card">
                
                <div class="admin-card-header">
                    <h3>📋 Liste des Utilisateurs</h3>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table compact-table">
                        <thead>
                            <tr>
                                <th>ID Client</th>
                                <th>Nom / Prénom</th>
                                <th>Email</th>
                                <th>Activité</th>
                                <th>Total Dépensé</th>
                                <th>Action (Phase 2)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="empty-row">
                                    Aucun utilisateur enregistré pour le moment.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>

    <footer class="admin-footer">
        <div class="footer-container">
            <p>&copy; 2026 Africa United - Interface d'administration sécurisée.</p>
        </div>
    </footer>

</body>
</html>