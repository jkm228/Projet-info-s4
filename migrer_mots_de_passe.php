<?php
$users_file = 'data/utilisateurs.json';

if (file_exists($users_file)) {
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    $compteur = 0;

    foreach ($utilisateurs as &$user) {
        $mdp = $user['mot_de_passe'] ?? '';
        
        // Si le mot de passe ne commence pas par $2y$ (le préfixe de Bcrypt), alors il est en clair, on le hache !
        if (!empty($mdp) && substr($mdp, 0, 4) !== '$2y$') {
            $user['mot_de_passe'] = password_hash($mdp, PASSWORD_BCRYPT);
            $compteur++;
        }
    }

    // On sauvegarde la base de données mise à jour
    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "<h2>Mise à niveau de la base de données réussie ! ✅</h2>";
    echo "<p>Nombre de mots de passe hachés en Bcrypt : <strong>$compteur</strong></p>";
    echo "<p style='color:green;'>Vous pouvez maintenant tester la connexion, vos identifiants restent les mêmes mais sont désormais sécurisés !</p>";
} else {
    echo "Erreur : Fichier utilisateurs.json introuvable.";
}
?>