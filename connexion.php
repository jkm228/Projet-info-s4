<?php 
$page_title = "Africa United - Connexion"; 
include 'includes/header.php'; 

$erreur = ""; // Message d'erreur par défaut vide

// Si le client a cliqué sur le bouton "Se connecter" (Envoi du formulaire en POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_saisi = $_POST['email'];
    $mdp_saisi = $_POST['password'];

    // 1. On lit la base de données JSON
    $json_data = file_get_contents('data/utilisateurs.json');
    $utilisateurs = json_decode($json_data, true);
    
    $utilisateur_trouve = false;

    // 2. On cherche si l'utilisateur existe
    foreach ($utilisateurs as $user) {
        if ($user['informations']['email'] == $email_saisi && $user['mot_de_passe'] == $mdp_saisi) {
            
            // BINGO ! L'utilisateur est trouvé et le mot de passe est bon.
            // 3. On stocke ses infos dans la "Session" pour s'en souvenir sur les autres pages
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_prenom'] = $user['informations']['prenom'];
            
            $utilisateur_trouve = true;

            // 4. On le redirige vers la bonne page selon son rôle !
            if ($user['role'] == 'admin' || $user['role'] == 'restaurateur') {
                header("Location: admin.php");
            } elseif ($user['role'] == 'livreur') {
                header("Location: livraison.php");
            } else {
                header("Location: profil.php"); // Les clients normaux vont sur leur profil
            }
            exit(); // On stoppe le code après une redirection
        }
    }

    // Si la boucle s'est terminée sans trouver l'utilisateur
    if (!$utilisateur_trouve) {
        $erreur = "Adresse e-mail ou mot de passe incorrect.";
    }
}
?>

    <main class="auth-page">
        <div class="form-container">
            <h2>Connexion</h2>
            
            <?php if($erreur != ""): ?>
                <p style="color: red; text-align: center; font-weight: bold; margin-bottom: 15px;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="connexion.php" method="POST">
                
                <div class="form-group">
                    <label for="email">Adresse E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>
            
            <div class="form-footer">
                Pas encore de compte ? <a href="inscription.php">S'inscrire ici</a>
            </div>
        </div>
    </main>

<?php 
include 'includes/footer.php'; 
?>