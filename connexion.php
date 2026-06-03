<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Africa United - Connexion"; 
include 'includes/header.php'; 

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_saisi = $_POST['email'] ?? '';
    $mdp_saisi = $_POST['password'] ?? '';

    $json_data = file_get_contents('data/utilisateurs.json');
    $utilisateurs = json_decode($json_data, true);
    
    if (is_array($utilisateurs)) {
        foreach ($utilisateurs as $user) {
            $mdp_enregistre = $user['mot_de_passe'] ?? '';
            $email_enregistre = $user['informations']['email'] ?? '';

            // 🔒 CORRECTIF SÉCURITÉ (PHASE 4) : Vérification du mot de passe haché
            if ($email_enregistre === $email_saisi && password_verify($mdp_saisi, $mdp_enregistre)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_prenom'] = $user['informations']['prenom'];
                $_SESSION['user_role'] = $user['role'];
                
                // 🚀 REDIRECTION INTELLIGENTE : Un écran adapté à chaque rôle
                if ($user['role'] === 'livreur') {
                    header("Location: livreur.php");
                } elseif ($user['role'] === 'restaurateur') {
                    header("Location: cuisine.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    // 👉 MODIFICATION ICI : On renvoie le client sur l'accueil
                    header("Location: accueil.php"); 
                }
                exit();
            }
        }
    }
    $erreur = "Adresse e-mail ou mot de passe incorrect.";
}
?>

<main class="auth-container">
    <div class="dashboard-card auth-card">
       <h1 class="auth-title">CONNEXION</h1>
        
        <?php if (!empty($erreur)): ?>
            <p class="auth-error"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <div class="form-group-block">
                <label class="form-label-bold">Adresse E-mail :</label>
                <input type="email" name="email" required class="form-input-field">
            </div>

            <div class="form-group-block password-margin">
                <label class="form-label-bold">Mot de passe :</label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="password" required class="form-input-field padding-right-toggle">
                    <span id="togglePassword" class="password-toggle-icon">👁️</span>
                </div>
            </div>

            <button type="submit" class="btn-submit btn-auth-submit">SE CONNECTER</button>
        </form>
        
        <p class="auth-redirect-text">Pas encore de compte ? <a href="inscription.php" class="auth-link-highlight">S'inscrire ici</a></p>
    </div>
</main>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
</script>

<?php include 'includes/footer.php'; ?>