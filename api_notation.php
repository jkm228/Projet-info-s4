<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['index_cmd']) && isset($data['note'])) {
    $users_file = 'data/utilisateurs.json';
    $utilisateurs = json_decode(file_get_contents($users_file), true);

    foreach ($utilisateurs as &$user) {
        if ($user['id'] === $_SESSION['user_id']) {
            $index = (int)$data['index_cmd'];
            
            if (isset($user['fidelite']['historique_commandes'][$index])) {
                $cmd = &$user['fidelite']['historique_commandes'][$index];
                
                // 🚀 LE CORRECTIF EST ICI : On accepte "Livrée" ET "Terminée"
                if (($cmd['statut'] === 'Livrée' || $cmd['statut'] === 'Terminée') && !isset($cmd['note'])) {
                    
                    $cmd['note'] = (int)$data['note'];
                    file_put_contents($users_file, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    echo json_encode(['success' => true]);
                    exit();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Impossible de noter cette commande (déjà notée ou non récupérée/livrée)']);
                    exit();
                }
            }
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur de données']);
?>