<?php
// On démarre la session pour savoir qui est connecté
session_start();

// On indique qu'on va répondre au format JSON (pour le JavaScript)
header('Content-Type: application/json');

// Sécurité : si personne n'est connecté, on bloque
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// 1. On récupère les données envoyées "en cachette" par le JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    $mis_a_jour = false;
    
    // 2. On cherche notre utilisateur et on modifie ses infos
    foreach ($utilisateurs as &$user) {
        if ($user['id'] === $_SESSION['user_id']) {
            $user['informations']['nom'] = htmlspecialchars($data['nom']);
            $user['informations']['prenom'] = htmlspecialchars($data['prenom']);
            $user['informations']['email'] = htmlspecialchars($data['email']);
            $user['informations']['telephone'] = htmlspecialchars($data['telephone']);
            $user['informations']['adresse_livraison'] = htmlspecialchars($data['adresse']);
            
            // On met aussi à jour le prénom en session pour l'affichage du header
            $_SESSION['user_prenom'] = htmlspecialchars($data['prenom']);
            $mis_a_jour = true;
            break;
        }
    }
    
    // 3. On sauvegarde dans le JSON et on répond au JavaScript
    if ($mis_a_jour) {
        file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès !']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue.']);
}
?>