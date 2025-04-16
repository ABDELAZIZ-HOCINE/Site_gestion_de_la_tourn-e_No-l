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
    <!-- Jouets ------------------------------------------------------------------------------------------------------------------>
    <div id="jouets" class="home-container">
        <div class="inner1-home-container">
            <div class="sep2"></div>
            <h2>Nos Jouets</h2>
            <div class="gift-list">
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $row): ?>
                        <div class="gift-item">
                            <img src="images/img/<?php echo htmlspecialchars($row['NOM_JOUET']); ?>.jpg" alt="<?php echo htmlspecialchars($row['NOM_JOUET']); ?>">
                            <p class="gift-name"><?php echo htmlspecialchars($row['NOM_JOUET']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun jouet trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
// Inclusion du pied de page
include("includes/cust-footer.php");
?>
