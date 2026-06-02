<?php 
$page_title = "Africa United - Inscription"; 
include 'includes/header.php'; 

// TRAITEMENT PHP DE L'INSCRIPTION (S'exécute uniquement si le JS a validé le formulaire)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);

    // On crée le nouveau profil
    $nouvel_utilisateur = [
        "id" => "USR" . rand(1000, 9999), 
        "role" => "client",
        // LE CORRECTIF EST ICI 👇 : On sort le mot de passe de la section "informations"
        "mot_de_passe" => $_POST['password'], 
        "informations" => [
            "nom" => htmlspecialchars($_POST['nom']),
            "prenom" => htmlspecialchars($_POST['prenom']),
            "email" => htmlspecialchars($_POST['email']),
            "telephone" => htmlspecialchars($_POST['telephone']),
            "adresse_livraison" => htmlspecialchars($_POST['adresse'])
        ],
        "fidelite" => [
            "points" => 0,
            "historique_commandes" => []
        ]
    ];

    $utilisateurs[] = $nouvel_utilisateur;
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // On redirige vers la connexion
    header("Location: connexion.php");
    exit();
}
?>

<main style="padding: 40px 20px; max-width: 500px; margin: 0 auto; min-height: 60vh;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" class="dashboard-card">
      <h1 style="text-align: center; color: #e74c3c; margin-bottom: 20px; font-size: 28px;">Rejoignez-nous</h1>
        
        <form action="inscription.php" method="POST" id="form-validation">
            
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Prénom :</label>
                <input type="text" name="prenom" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nom :</label>
                <input type="text" name="nom" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Email (max 30 chars) :</label>
                <input type="email" name="email" id="email" maxlength="30" class="count-chars" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                
                <div style="display: flex; justify-content: space-between; font-size: 0.85em; margin-top: 5px;">
                    <span id="error-email" class="error-msg" style="color: #e74c3c; font-weight: bold;"></span>
                    <span id="counter-email" style="color: #7f8c8d;">0 / 30</span>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Mot de passe (max 20 chars) :</label>
                
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" name="password" id="password" maxlength="20" class="count-chars" required style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                    
                    <span id="togglePassword" style="position: absolute; right: 10px; cursor: pointer; font-size: 1.2em;">👁️</span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.85em; margin-top: 5px;">
                    <span id="error-password" class="error-msg" style="color: #e74c3c; font-weight: bold;"></span>
                    <span id="counter-password" style="color: #7f8c8d;">0 / 20</span>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Téléphone :</label>
                <input type="tel" name="telephone" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Adresse complète :</label>
                <textarea name="adresse" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; height: 80px;"></textarea>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%; padding: 12px; font-size: 1.1em; background-color: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">M'inscrire</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>