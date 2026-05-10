<?php
session_start();
header('Content-Type: application/json');

// Sécurité : Seul un administrateur peut faire ça
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    // ON ENLÈVE intval() POUR ACCEPTER LES TEXTES (STRINGS) ET LES CHIFFRES
    $user_id = $data['user_id']; 
    
    // Protection anti-gaffe : on utilise == au lieu de ===
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous bloquer vous-même !']);
        exit();
    }

    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);
    $modifie = false;

    foreach ($utilisateurs as &$user) {
        // ON UTILISE == AU LIEU DE === POUR NE PAS BLOQUER SUR LE TYPE DE DONNÉE
        if ($user['id'] == $user_id) { 
            $est_bloque = isset($user['bloque']) ? $user['bloque'] : false;
            
            // On inverse son état
            $user['bloque'] = !$est_bloque; 
            
            $modifie = true;
            break;
        }
    }

    if ($modifie) {
        file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    }
}
?>