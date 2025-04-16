<?php
session_start();
include_once("../connexion-base/connex.inc.php");

// Fonction pour gérer les valeurs NULL
function cleanPostValue($value) {
    return (!empty($value) || $value === '0') ? $value : null;
}

// Connexion à la base de données Oracle
$idconn = connexoci("my-param", "oracle2");
if (!$idconn) {
    $e = oci_error();
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e['message']));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-windows.css">
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics - Gestion Fournisseurs</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

    <main>
        <div id="gestion-fournisseurs" class="home-container">
            <div class="inner1-home-container">
                <h2>Gestion des Fournisseurs</h2>
                <div class="sep2"></div>
                
                <?php if (isset($_SESSION["user_id"])): ?>
                    <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Fournisseurs !</p>

                    <!-- Section Liste des Fournisseurs -->
                    <div class="liste-fournisseurs">
                        <h3>Liste des Fournisseurs</h3>
                        <fieldset>
                            <?php
                            // Récupération de la liste des fournisseurs
                            $sql_select = "SELECT id_fournisseur, nom_fournisseur FROM les_fournisseurs ORDER BY id_fournisseur";
                            $stid_select = oci_parse($idconn, $sql_select);
                            
                            if (!oci_execute($stid_select)) {
                                $e = oci_error($stid_select);
                                echo "<p class='error'>Erreur lors de la récupération des fournisseurs: " . htmlspecialchars($e['message']) . "</p>";
                            } else {
                                // Vérification s'il y a des résultats
                                $numrows = oci_fetch_all($stid_select, $results);
                                
                                if ($numrows > 0) {
                                    echo '<table class="table-style">';
                                    echo '<thead><tr>
                                            <th>ID Fournisseur</th>
                                            <th>Nom Fournisseur</th>
                                        </tr></thead>';
                                    echo '<tbody>';
                                    
                                    // Ré-exécuter la requête pour parcourir les résultats
                                    oci_execute($stid_select);
                                    while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>'.htmlspecialchars($row['ID_FOURNISSEUR']).'</td>';
                                        echo '<td>'.htmlspecialchars($row['NOM_FOURNISSEUR']).'</td>';
                                        echo '</tr>';
                                    }
                                    
                                    echo '</tbody>';
                                    echo '</table>';
                                } else {
                                    echo "<p class='error'>Aucun fournisseur trouvé dans la base de données.</p>";
                                }
                            }
                            
                            // Libération des ressources
                            oci_free_statement($stid_select);
                            oci_close($idconn);
                            ?>
                        </fieldset>
                    </div>
                    
                <?php else: ?>
                    <p>Veuillez vous <a href="compte.php">connecter</a> pour accéder à cette page.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include("../includes/administrator-footer.php"); ?>
</body>
</html>