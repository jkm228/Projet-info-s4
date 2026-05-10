<?php
session_start();
header('Content-Type: application/json');

// Sécurité : Il faut être connecté en tant que client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['index_cmd']) && isset($data['note'])) {
    $note = intval($data['note']);
    
    // On s'assure que la note est bien entre 1 et 5
    if ($note < 1 || $note > 5) {
        echo json_encode(['success' => false, 'message' => 'Note invalide']);
        exit();
    }

    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    
    $modifie = false;
    foreach ($utilisateurs as &$user) {
        if ($user['id'] === $_SESSION['user_id']) {
            $idx = $data['index_cmd'];
            if (isset($user['fidelite']['historique_commandes'][$idx])) {
                // On vérifie que la commande est bien livrée et qu'elle n'a pas déjà de note
                if ($user['fidelite']['historique_commandes'][$idx]['statut'] === 'Livrée' && !isset($user['fidelite']['historique_commandes'][$idx]['note'])) {
                    $user['fidelite']['historique_commandes'][$idx]['note'] = $note;
                    $modifie = true;
                    break;
                }
            }
        }
    }

    if ($modifie) {
        file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossible de noter cette commande (déjà notée ou non livrée)']);
    }
}
?>