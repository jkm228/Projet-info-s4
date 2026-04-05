<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔒 SÉCURITÉ : On vérifie que la personne est bien admin ou restaurateur
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'restaurateur')) {
    header("Location: accueil.php");
    exit();
}

$page_title = "Africa United - Administration"; 
include 'includes/header.php'; 

// 1. On lit le fichier des utilisateurs
$json_data = file_get_contents('data/utilisateurs.json');
$utilisateurs = json_decode($json_data, true);

// 2. On crée une liste vide pour rassembler TOUTES les commandes
$toutes_les_commandes = [];

// 3. On fouille dans chaque utilisateur pour extraire ses commandes
foreach ($utilisateurs as $user) {
    if (!empty($user['fidelite']['historique_commandes'])) {
        foreach ($user['fidelite']['historique_commandes'] as $commande) {
            // On ajoute le nom du client DANS la commande pour savoir à qui elle est
            $commande['client'] = $user['informations']['prenom'] . ' ' . $user['informations']['nom'];
            $commande['email'] = $user['informations']['email'];
            $toutes_les_commandes[] = $commande;
        }
    }
}

// On inverse la liste pour avoir les commandes les plus récentes en premier
$toutes_les_commandes = array_reverse($toutes_les_commandes);
?>

    <main class="admin-page" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; min-height: 60vh;">
        
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="color: #e74c3c;">👨‍🍳 Espace Administration</h1>
            <p>Bienvenue, <strong><?php echo $_SESSION['user_prenom']; ?></strong>. Voici le tableau de bord de votre restaurant.</p>
        </div>

        <section style="margin-bottom: 50px;">
            <h2 style="border-bottom: 3px solid #e74c3c; padding-bottom: 10px;">📦 Dernières Commandes Clients</h2>
            
            <?php if(empty($toutes_les_commandes)): ?>
                <p style="padding: 20px; background: #f9f9f9; text-align: center; border-radius: 5px;">Aucune commande pour le moment.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: white; margin-top: 20px;">
                    <thead style="background-color: #2c3e50; color: white;">
                        <tr style="text-align: left;">
                            <th style="padding: 15px;">Date</th>
                            <th style="padding: 15px;">Client</th>
                            <th style="padding: 15px;">Articles commandés</th>
                            <th style="padding: 15px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($toutes_les_commandes as $cmd): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; font-weight: bold;"><?php echo $cmd['date'] ?? $cmd['date_passage'] ?? 'Inconnue'; ?></td>
                            <td style="padding: 15px;">
                                <?php echo $cmd['client']; ?><br>
                                <span style="font-size: 0.85em; color: #7f8c8d;"><?php echo $cmd['email']; ?></span>
                            </td>
                            <td style="padding: 15px; font-size: 0.9em;">
                                <?php echo implode('<br> • ', $cmd['articles']); ?>
                            </td>
                            <td style="padding: 15px; text-align: right; color: #e74c3c; font-weight: bold; font-size: 1.1em;">
                                <?php echo number_format($cmd['total'], 2); ?> €
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>


        <section>
            <h2 style="border-bottom: 3px solid #3498db; padding-bottom: 10px;">👥 Base de données Clients & Staff</h2>
            
            <table style="width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: white; margin-top: 20px;">
                <thead style="background-color: #f4f4f4; border-bottom: 2px solid #ddd; text-align: left;">
                    <tr>
                        <th style="padding: 15px;">ID</th>
                        <th style="padding: 15px;">Client / Staff</th>
                        <th style="padding: 15px;">Rôle</th>
                        <th style="padding: 15px; text-align: center;">Actions (Phase 2)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><?php echo $user['id']; ?></td>
                        <td style="padding: 15px;">
                            <strong><?php echo $user['informations']['prenom'] . ' ' . $user['informations']['nom']; ?></strong><br>
                            <span style="font-size: 0.85em; color: #7f8c8d;"><?php echo $user['informations']['email']; ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <strong style="color: <?php echo ($user['role'] == 'admin' || $user['role'] == 'restaurateur') ? '#e74c3c' : '#27ae60'; ?>;">
                                <?php echo strtoupper($user['role']); ?>
                            </strong>
                        </td>
                        
                        <td style="padding: 15px; text-align: center;">
                            <?php if($user['role'] == 'client'): ?>
                                <select style="padding: 5px; margin-right: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option>Standard</option>
                                    <option>🌟 VIP</option>
                                    <option>👑 Premium</option>
                                </select>
                                <select style="padding: 5px; margin-right: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option>Remise 0%</option>
                                    <option>Remise 10%</option>
                                    <option>Remise 20%</option>
                                </select>
                                <button style="padding: 6px 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">Bloquer</button>
                            <?php else: ?>
                                <span style="color: #95a5a6; font-style: italic;">Non applicable</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>

<?php 
include 'includes/footer.php'; 
?>