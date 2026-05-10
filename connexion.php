<?php 
$page_title = "Africa United - Connexion"; 
include 'includes/header.php'; 

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On sécurise la récupération des données envoyées par le formulaire
    $email_saisi = $_POST['email'] ?? '';
    $mdp_saisi = $_POST['password'] ?? '';

    $json_data = file_get_contents('data/utilisateurs.json');
    $utilisateurs = json_decode($json_data, true);
    
 foreach ($utilisateurs as $user) {
        // LE CORRECTIF EST ICI : mot_de_passe est à la racine, pas dans 'informations'
        $mdp_enregistre = $user['mot_de_passe'] ?? '';
        $email_enregistre = $user['informations']['email'] ?? '';

        // Si ça correspond parfaitement
        if ($email_enregistre === $email_saisi && $mdp_enregistre === $mdp_saisi) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_prenom'] = $user['informations']['prenom'];
            $_SESSION['user_role'] = $user['role'];
            
            header("Location: profil.php");
            exit();
        }
    }
    
    // Si la boucle se termine sans avoir redirigé, c'est que c'est incorrect
    $erreur = "Adresse e-mail ou mot de passe incorrect.";
}
?>

<main style="padding: 40px 20px; max-width: 500px; margin: 0 auto; min-height: 60vh;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" class="dashboard-card">
       <h1 style="text-align: center; margin-bottom: 20px; font-size: 28px; letter-spacing: 2px;">CONNEXION</h1>
        
        <?php if (!empty($erreur)): ?>
            <p style="color: #e74c3c; text-align: center; font-weight: bold; margin-bottom: 15px;"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form action="connexion.php" method="POST" id="form-validation">
            
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Adresse E-mail :</label>
                <input type="email" name="email" id="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                
                <span id="error-email" class="error-msg" style="color: #e74c3c; font-weight: bold; font-size: 0.85em; display: block; margin-top: 5px;"></span>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Mot de passe :</label>
                
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" name="password" id="password" required style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                    <span id="togglePassword" style="position: absolute; right: 10px; cursor: pointer; font-size: 1.2em;">👁️</span>
                </div>
                
                <span id="error-password" class="error-msg" style="color: #e74c3c; font-weight: bold; font-size: 0.85em; display: block; margin-top: 5px;"></span>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; padding: 12px; font-size: 1.1em; background-color: #f1c40f; color: black; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">SE CONNECTER</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">Pas encore de compte ? <a href="inscription.php" style="color: #e74c3c; font-weight: bold;">S'inscrire ici</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>