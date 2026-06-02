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

            if ($email_enregistre === $email_saisi && $mdp_enregistre === $mdp_saisi) {
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
                    header("Location: profil.php"); // Client
                }
                exit();
            }
        }
    }
    $erreur = "Adresse e-mail ou mot de passe incorrect.";
}
?>

<main style="padding: 40px 20px; max-width: 500px; margin: 0 auto; min-height: 60vh;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" class="dashboard-card">
       <h1 style="text-align: center; margin-bottom: 20px; font-size: 28px; letter-spacing: 2px;">CONNEXION</h1>
        
        <?php if (!empty($erreur)): ?>
            <p style="color: #e74c3c; text-align: center; font-weight: bold; margin-bottom: 15px;"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form action="connexion.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Adresse E-mail :</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Mot de passe :</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" name="password" id="password" required style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #ccc; border-radius: 5px;">
                    <span id="togglePassword" style="position: absolute; right: 10px; cursor: pointer; font-size: 1.2em;">👁️</span>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; padding: 12px; font-size: 1.1em; background-color: #f1c40f; color: black; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">SE CONNECTER</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">Pas encore de compte ? <a href="inscription.php" style="color: #e74c3c; font-weight: bold;">S'inscrire ici</a></p>
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