<?php 
$page_title = "Africa United - Inscription"; 
include 'includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);

    $nouvel_utilisateur = [
        "id" => "USR" . rand(1000, 9999), 
        "role" => "client", 
        // 🔒 CORRECTIF : On hache le mot de passe avant de l'enregistrer !
        "mot_de_passe" => password_hash($_POST['password'], PASSWORD_BCRYPT), 
        "informations" => [
            "nom" => htmlspecialchars($_POST['nom']), "prenom" => htmlspecialchars($_POST['prenom']),
            "email" => htmlspecialchars($_POST['email']), "telephone" => htmlspecialchars($_POST['telephone']),
            "adresse_livraison" => htmlspecialchars($_POST['adresse'])
        ],
        "fidelite" => [ "points" => 0, "historique_commandes" => [] ]
    ];

    $utilisateurs[] = $nouvel_utilisateur;
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: connexion.php"); exit();
}
?>

<main class="main-container-500">
    <div class="dashboard-card auth-card">
      <h1 class="title-auth-red">Rejoignez-nous</h1>
        <form action="inscription.php" method="POST" id="form-validation">
            <div class="form-group-block">
                <label class="form-label-bold">Prénom :</label>
                <input type="text" name="prenom" required class="form-input-field">
            </div>
            <div class="form-group-block">
                <label class="form-label-bold">Nom :</label>
                <input type="text" name="nom" required class="form-input-field">
            </div>
            <div class="form-group-block">
                <label class="form-label-bold">Email (max 30 chars) :</label>
                <input type="email" name="email" id="email" maxlength="30" class="form-input-field count-chars" required>
                <div class="auth-counter-flex">
                    <span id="error-email" class="auth-error-msg"></span>
                    <span id="counter-email" class="text-secondary">0 / 30</span>
                </div>
            </div>
            <div class="form-group-block">
                <label class="form-label-bold">Mot de passe (max 20 chars) :</label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="password" maxlength="20" class="form-input-field count-chars padding-right-toggle" required>
                    <span id="togglePassword" class="password-toggle-icon">👁️</span>
                </div>
                <div class="auth-counter-flex">
                    <span id="error-password" class="auth-error-msg"></span>
                    <span id="counter-password" class="text-secondary">0 / 20</span>
                </div>
            </div>
            <div class="form-group-block password-margin">
                <label class="form-label-bold">Téléphone :</label>
                <input type="tel" name="telephone" required class="form-input-field">
            </div>
            <div class="form-group-block password-margin">
                <label class="form-label-bold">Adresse complète :</label>
                <textarea name="adresse" required class="form-textarea-field"></textarea>
            </div>
            <button type="submit" class="btn-submit btn-auth-register">M'inscrire</button>
        </form>
    </div>
</main>
<?php include 'includes/footer.php'; ?>