<?php 
$page_title = "Africa United - Inscription"; 
include 'includes/header.php'; 

$message_erreur = "";

// Si le formulaire vient d'être soumis (clic sur le bouton)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. On lit la base de données actuelle
    $fichier_json = 'data/utilisateurs.json';
    $json_data = file_get_contents($fichier_json);
    $utilisateurs = json_decode($json_data, true);

    $email_saisi = $_POST['email'];
    $email_existe = false;

    // 2. On vérifie si l'email n'est pas déjà pris
    foreach ($utilisateurs as $user) {
        if ($user['informations']['email'] == $email_saisi) {
            $email_existe = true;
            break; // On arrête de chercher, on a trouvé un doublon
        }
    }

    if ($email_existe) {
        $message_erreur = "Cette adresse e-mail est déjà utilisée. Veuillez vous connecter.";
    } else {
        // 3. On crée un identifiant unique (ex: C-004)
        // On compte combien il y a d'utilisateurs et on ajoute 1
        $nouvel_id = "C-" . str_pad(count($utilisateurs) + 1, 3, "0", STR_PAD_LEFT);

        // 4. On prépare la fiche du nouveau client exactement dans le même format que les autres
        $nouveau_client = [
            "id" => $nouvel_id,
            "role" => "client",
            "informations" => [
                "nom" => htmlspecialchars($_POST['nom']), // htmlspecialchars protège contre les failles de sécurité
                "prenom" => htmlspecialchars($_POST['prenom']),
                "email" => $email_saisi,
                "telephone" => htmlspecialchars($_POST['telephone']),
                "adresse_livraison" => htmlspecialchars($_POST['adresse'])
            ],
            "mot_de_passe" => $_POST['password'], // Dans un vrai site pro, on crypterait ça avec password_hash() !
            "fidelite" => [
                "points" => 0,
                "historique_commandes" => []
            ]
        ];

        // 5. On ajoute ce nouveau client à la liste
        $utilisateurs[] = $nouveau_client;

        // 6. On réécrit le fichier JSON pour sauvegarder définitivement !
        file_put_contents($fichier_json, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 7. On redirige le client vers la page de connexion
        header("Location: connexion.php");
        exit();
    }
}
?>

    <main class="auth-page">
        <div class="form-container">
            <h2>Créer un compte</h2>
            
            <?php if($message_erreur != ""): ?>
                <p style="color: red; text-align: center; font-weight: bold; margin-bottom: 15px;">
                    <?php echo $message_erreur; ?>
                </p>
            <?php endif; ?>

            <form action="inscription.php" method="POST">
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required placeholder="Votre nom">
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
                </div>

                <div class="form-group">
                    <label for="email">Adresse E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com">
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse de livraison</label>
                    <input type="text" id="adresse" name="adresse" required placeholder="123 Rue de Paris...">
                </div>

                <div class="form-group">
                    <label for="telephone">Numéro de téléphone</label>
                    <input type="tel" id="telephone" name="telephone" required placeholder="06 12 34 56 78">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-submit">S'inscrire</button>
            </form>
            
            <div class="form-footer">
                Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a>
            </div>
        </div>
    </main>

<?php 
include 'includes/footer.php'; 
?>