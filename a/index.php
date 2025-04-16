<?php
    session_start();
    // Inclusion des paramètres de connexion
    include_once("../connexion-base/connex.inc.php");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-index.css">
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics</title>
</head>
<?php
    // Inclusion de l'en-tête
    include("../includes/administrator-header.php");
?>


<main>
    <!--########################################################################################################################-->
    <!-- acceuil ----------------------------------------------------------------------------------------------------------------->
    <div id="accueil" class="home-container">
        <h1>Tableaux de bord</h1>
        <div class="separateur"></div>
        <ul class="win_list">      
            <!-- Ajout des icônes avec FontAwesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <!-- Gestion des personnes -->
            <div class="inner-home-container">
                <li><a href="gestion_enfants.php" class="menu-item"><i class="fas fa-child"></i> Les enfants</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_elfes.php" class="menu-item"><i class="fas fa-hat-wizard"></i> Les elfes</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_rennes.php" class="menu-item"><i class="fas fa-horse"></i> Les rennes</a></li>
            </div>
            <!-- Gestion des équipes -->
            <div class="inner-home-container">
                <li><a href="gestion_equipes_fabrication.php" class="menu-item"><i class="fas fa-tools"></i> Les équipes de fabrication</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_equipes_logistiques.php" class="menu-item"><i class="fas fa-truck"></i> Les équipes logistiques</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_chef_equipe.php" class="menu-item"><i class="fas fa-user-tie"></i> Chef d'équipe</a></li>
            </div>

            <!-- Gestion des ressources -->
            <div class="inner-home-container">
                <li><a href="gestion_matieres_premieres.php" class="menu-item"><i class="fas fa-cubes"></i> Les matières premières</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_fournisseurs.php" class="menu-item"><i class="fas fa-truck-loading"></i> Les fournisseurs</a></li>
            </div>

            <!-- Gestion des ateliers et entrepôts -->
            <div class="inner-home-container">
                <li><a href="gestion_ateliers.php" class="menu-item"><i class="fas fa-industry"></i> Les ateliers</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_entrepots.php" class="menu-item"><i class="fas fa-warehouse"></i> Les entrepôts</a></li>
            </div>

            <div class="inner-home-container">
                <li><a href="gestion_historique.php" class="menu-item"><i class="fas fa-history"></i> L'historique de fabrication</a></li>
            </div>

            <!-- Tables de liaison -->
            <div class="inner-home-container">
                <li><a href="gestion_commande_jouets.php" class="menu-item"><i class="fas fa-shopping-cart"></i> Commande jouets</a></li>
            </div>
            <!-- Gestion des productions -->
            <div class="inner-home-container">
                <li><a href="gestion_specialites.php" class="menu-item"><i class="fas fa-gift"></i> Les spécialités</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_jouets.php" class="menu-item"><i class="fas fa-gamepad"></i> Les jouets</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_cadeaux.php" class="menu-item"><i class="fas fa-box"></i> Les cadeaux</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_substituer_par.php" class="menu-item"><i class="fas fa-exchange-alt"></i> Substitution des jouets</a></li>
            </div>

            <div class="inner-home-container">
                <li><a href="gestion_commande.php" class="menu-item"><i class="fas fa-dolly"></i> Commande matière première</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_confie_au.php" class="menu-item"><i class="fas fa-hands-helping"></i> Commande confiée au ST</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_remplace_elfe.php" class="menu-item"><i class="fas fa-user-slash"></i> Table des absences</a></li>
            </div>

            <!-- Gestion des transports et livraisons -->
            <div class="inner-home-container">
                <li><a href="gestion_itineraires.php" class="menu-item"><i class="fas fa-map-marked-alt"></i> Les itinéraires de distribution</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_livraisons.php" class="menu-item"><i class="fas fa-shipping-fast"></i> Les livraisons</a></li>
            </div>
            <div class="inner-home-container">
                <li><a href="gestion_traineaux.php" class="menu-item"><i class="fas fa-sleigh"></i> Les traîneaux</a></li>
            </div>
        </ul>
    </div>

</main>

<?php
// Inclusion du pied de page
include("../includes/administrator-footer.php");
?>
