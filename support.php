<?php
    // Inclusion des paramètres de connexion
    include_once("connexion-base/connex.inc.php");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="css/style-of-my-web-site-cust-index.css">
    <link rel="icon" type="image/x-icon" href="images/icons/icon.png">
    <title>SantaLogistics</title>
</head>
<?php
    // Inclusion de l'en-tête
    include("includes/cust-header.php");
?>

<main>
    <!-- Support ----------------------------------------------------------------------------------------------------------------->
    <div id="support" class="home-container">
        <div class="inner1-home-container">
            <div class="sep2"></div>
            <h2>Support & Assistance</h2>
            <p>Contactez-nous pour toute question.</p>
        </div>
    </div>
</main>

<?php
// Inclusion du pied de page
include("includes/cust-footer.php");
?>
