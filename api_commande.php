<?php
session_start();
header('Content-Type: application/json');

// SÉCURITÉ MISE À JOUR : On autorise aussi le 'livreur' !
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'restaurateur', 'livreur'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email_client']) && isset($data['index_cmd']) && isset($data['nouveau_statut'])) {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    $modifie = false;
    foreach ($utilisateurs as &$user) {
        if ($user['informations']['email'] === $data['email_client']) {
            $idx = $data['index_cmd'];
            if (isset($user['fidelite']['historique_commandes'][$idx])) {
                // Mise à jour du statut
                $user['fidelite']['historique_commandes'][$idx]['statut'] = $data['nouveau_statut'];
                
                // Si on assigne un livreur
                if (isset($data['livreur_id'])) {
                    $user['fidelite']['historique_commandes'][$idx]['livreur_id'] = $data['livreur_id'];
                }
                
                $modifie = true;
                break;
            }
        }
    }

    if ($modifie) {
        file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
}
?>