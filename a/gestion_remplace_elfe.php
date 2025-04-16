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

// TRAITEMENT DE L'AJOUT D'UNE ABSENCE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_elfe_va_etre_remplacer']) && isset($_POST['id_elfe_va_remplacer']) && isset($_POST['date_absence']) && isset($_POST['ajouter'])) {
        // Récupération des données du formulaire
        $id_elfe_va_etre_remplacer = $_POST['id_elfe_va_etre_remplacer'];
        $id_elfe_va_remplacer = $_POST['id_elfe_va_remplacer'];
        $date_absence = $_POST['date_absence'];

        // Valider et sécuriser les données
        $id_elfe_va_etre_remplacer = htmlspecialchars($id_elfe_va_etre_remplacer);
        $id_elfe_va_remplacer = htmlspecialchars($id_elfe_va_remplacer);
        $date_absence = htmlspecialchars($date_absence);

        try {
            // Vérifier que les elfes existent
            $sql_check_elfe1 = "SELECT COUNT(*) AS count FROM les_elfes WHERE id_elfe = :id_elfe1";
            $stid_check_elfe1 = oci_parse($idconn, $sql_check_elfe1);
            oci_bind_by_name($stid_check_elfe1, ':id_elfe1', $id_elfe_va_etre_remplacer);
            oci_execute($stid_check_elfe1);
            $row = oci_fetch_assoc($stid_check_elfe1);
            $count_elfe1 = $row['COUNT'];
            oci_free_statement($stid_check_elfe1);

            $sql_check_elfe2 = "SELECT COUNT(*) AS count FROM les_elfes WHERE id_elfe = :id_elfe2";
            $stid_check_elfe2 = oci_parse($idconn, $sql_check_elfe2);
            oci_bind_by_name($stid_check_elfe2, ':id_elfe2', $id_elfe_va_remplacer);
            oci_execute($stid_check_elfe2);
            $row = oci_fetch_assoc($stid_check_elfe2);
            $count_elfe2 = $row['COUNT'];
            oci_free_statement($stid_check_elfe2);

            if ($count_elfe1 == 0 || $count_elfe2 == 0) {
                $_SESSION['message'] = "<p class='error'>Un ou plusieurs elfes spécifiés n'existent pas.</p>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }

            // Vérifier que l'elfe remplaçant n'est pas déjà en remplacement à cette date
            $sql_check_remplacement = "SELECT COUNT(*) AS count FROM remplace_elfe 
                                      WHERE id_elfe_va_remplacer = :id_elfe_remplacant 
                                      AND date_absence = TO_DATE(:date_absence, 'YYYY-MM-DD')";
            $stid_check_remplacement = oci_parse($idconn, $sql_check_remplacement);
            oci_bind_by_name($stid_check_remplacement, ':id_elfe_remplacant', $id_elfe_va_remplacer);
            oci_bind_by_name($stid_check_remplacement, ':date_absence', $date_absence);
            oci_execute($stid_check_remplacement);
            $row = oci_fetch_assoc($stid_check_remplacement);
            $count_remplacement = $row['COUNT'];
            oci_free_statement($stid_check_remplacement);

            if ($count_remplacement > 0) {
                $_SESSION['message'] = "<p class='error'>Cet elfe est déjà en remplacement à cette date.</p>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }

            // Vérifier que l'elfe remplacé n'est pas déjà en remplacement à cette date
            $sql_check_absence = "SELECT COUNT(*) AS count FROM remplace_elfe 
                                 WHERE id_elfe_va_etre_remplacer = :id_elfe_remplace 
                                 AND date_absence = TO_DATE(:date_absence, 'YYYY-MM-DD')";
            $stid_check_absence = oci_parse($idconn, $sql_check_absence);
            oci_bind_by_name($stid_check_absence, ':id_elfe_remplace', $id_elfe_va_etre_remplacer);
            oci_bind_by_name($stid_check_absence, ':date_absence', $date_absence);
            oci_execute($stid_check_absence);
            $row = oci_fetch_assoc($stid_check_absence);
            $count_absence = $row['COUNT'];
            oci_free_statement($stid_check_absence);

            if ($count_absence > 0) {
                $_SESSION['message'] = "<p class='error'>Cet elfe est déjà en absence à cette date.</p>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }

            // Ajouter le remplacement
            $sql_insert = "INSERT INTO remplace_elfe (id_elfe_va_remplacer, id_elfe_va_etre_remplacer, date_absence) 
                          VALUES (:id_elfe_remplacant, :id_elfe_remplace, TO_DATE(:date_absence, 'YYYY-MM-DD'))";
            $stid_insert = oci_parse($idconn, $sql_insert);
            oci_bind_by_name($stid_insert, ':id_elfe_remplacant', $id_elfe_va_remplacer);
            oci_bind_by_name($stid_insert, ':id_elfe_remplace', $id_elfe_va_etre_remplacer);
            oci_bind_by_name($stid_insert, ':date_absence', $date_absence);

            if (oci_execute($stid_insert)) {
                $_SESSION['message'] = "<p class='success'>Remplacement enregistré avec succès.</p>";
            } else {
                $_SESSION['message'] = "<p class='error'>Erreur lors de l'enregistrement du remplacement.</p>";
            }
            
            oci_free_statement($stid_insert);
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }
    // TRAITEMENT DE LA SUPPRESSION D'UNE ABSENCE
    elseif (isset($_POST['id_elfe_va_remplacer']) && isset($_POST['id_elfe_va_etre_remplacer']) && isset($_POST['date_absence']) && isset($_POST['supprimer'])) {
        $id_elfe_va_remplacer = $_POST['id_elfe_va_remplacer'];
        $id_elfe_va_etre_remplacer = $_POST['id_elfe_va_etre_remplacer'];
        $date_absence = $_POST['date_absence'];
        
        try {
            // Supprimer le remplacement
            $sql_delete = "DELETE FROM remplace_elfe 
                          WHERE id_elfe_va_remplacer = :id_elfe_remplacant 
                          AND id_elfe_va_etre_remplacer = :id_elfe_remplace 
                          AND date_absence = TO_DATE(:date_absence, 'YYYY-MM-DD')";
            $stid_delete = oci_parse($idconn, $sql_delete);
            oci_bind_by_name($stid_delete, ':id_elfe_remplacant', $id_elfe_va_remplacer);
            oci_bind_by_name($stid_delete, ':id_elfe_remplace', $id_elfe_va_etre_remplacer);
            oci_bind_by_name($stid_delete, ':date_absence', $date_absence);
            
            if (oci_execute($stid_delete)) {
                $_SESSION['message'] = "<p class='success'>Remplacement supprimé avec succès.</p>";
            } else {
                $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression du remplacement.</p>";
            }
            
            oci_free_statement($stid_delete);
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }
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
    <title>SantaLogistics - Gestion des Absences</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

<main>
    <div id="gestion-absences" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Absences et Remplacements</h2>
            <div class="sep2"></div>
            
            <?php if (isset($_SESSION["user_id"])): ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des absences !</p>

                <!-- Affichage des messages de session -->
                <?php if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                } ?>

                <!-- Section Liste des Absences -->
                <div class="liste-absences">
                    <h3>Liste des Absences Programmées</h3>
                    <fieldset>
                        <?php
                        // Récupération de la liste des elfes
                        $sql_elfes = "SELECT id_elfe, nom FROM les_elfes ORDER BY nom";
                        $stid_elfes = oci_parse($idconn, $sql_elfes);
                        oci_execute($stid_elfes);
                        $elfes = [];
                        while ($row = oci_fetch_array($stid_elfes, OCI_ASSOC)) {
                            $elfes[$row['ID_ELFE']] = $row['NOM'];
                        }
                        oci_free_statement($stid_elfes);

                        // Récupération de la liste des absences
                        $sql_select = "SELECT r.id_elfe_va_remplacer, e1.nom AS nom_remplacant, 
                                              r.id_elfe_va_etre_remplacer, e2.nom AS nom_remplace, 
                                              TO_CHAR(r.date_absence, 'DD/MM/YYYY') AS date_absence
                                       FROM remplace_elfe r
                                       JOIN les_elfes e1 ON r.id_elfe_va_remplacer = e1.id_elfe
                                       JOIN les_elfes e2 ON r.id_elfe_va_etre_remplacer = e2.id_elfe
                                       ORDER BY r.date_absence DESC";
                        $stid_select = oci_parse($idconn, $sql_select);
                        
                        if (!oci_execute($stid_select)) {
                            $e = oci_error($stid_select);
                            echo "<p class='error'>Erreur lors de la récupération des absences: " . htmlspecialchars($e['message']) . "</p>";
                        } else {
                            // Vérification s'il y a des résultats
                            $numrows = oci_fetch_all($stid_select, $results);
                            
                            if ($numrows > 0) {
                                echo '<table class="table-style">';
                                echo '<thead><tr>
                                        <th>Date</th>
                                        <th>Elfe absent</th>
                                        <th>Remplaçant</th>
                                        <th>Actions</th>
                                      </tr></thead>';
                                echo '<tbody>';
                                
                                // Ré-exécuter la requête pour parcourir les résultats
                                oci_execute($stid_select);
                                while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.htmlspecialchars($row['DATE_ABSENCE']).'</td>';
                                    echo '<td>'.htmlspecialchars($row['NOM_REMPLACE']).' ('.htmlspecialchars($row['ID_ELFE_VA_ETRE_REMPLACER']).')</td>';
                                    echo '<td>'.htmlspecialchars($row['NOM_REMPLACANT']).' ('.htmlspecialchars($row['ID_ELFE_VA_REMPLACER']).')</td>';
                                    echo '<td class="actions-cell">';
                                    
                                    // Formulaire de suppression
                                    echo '<form method="post" class="inline-form" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ce remplacement?\');">';
                                    echo '<input type="hidden" name="id_elfe_va_remplacer" value="'.htmlspecialchars($row['ID_ELFE_VA_REMPLACER']).'">';
                                    echo '<input type="hidden" name="id_elfe_va_etre_remplacer" value="'.htmlspecialchars($row['ID_ELFE_VA_ETRE_REMPLACER']).'">';
                                    echo '<input type="hidden" name="date_absence" value="'.date('Y-m-d', strtotime(str_replace('/', '-', $row['DATE_ABSENCE']))).'">';
                                    echo '<button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "<p class='error'>Aucune absence programmée trouvée dans la base de données.</p>";
                            }
                        }
                        ?>
                    </fieldset>
                    
                    <!-- Formulaire d'ajout d'une nouvelle absence -->
                    <h3>Programmer une absence</h3>
                    <fieldset>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="date_absence">Date d'absence:</label>
                                <input type="date" id="date_absence" name="date_absence" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_elfe_va_etre_remplacer">Elfe absent:</label>
                                <select id="id_elfe_va_etre_remplacer" name="id_elfe_va_etre_remplacer" required>
                                    <option value="">Sélectionnez un elfe</option>
                                    <?php
                                    foreach ($elfes as $id => $nom) {
                                        echo '<option value="'.htmlspecialchars($id).'">'.htmlspecialchars($nom).' ('.htmlspecialchars($id).')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_elfe_va_remplacer">Elfe remplaçant:</label>
                                <select id="id_elfe_va_remplacer" name="id_elfe_va_remplacer" required>
                                    <option value="">Sélectionnez un elfe</option>
                                    <?php
                                    foreach ($elfes as $id => $nom) {
                                        echo '<option value="'.htmlspecialchars($id).'">'.htmlspecialchars($nom).' ('.htmlspecialchars($id).')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <button type="submit" name="ajouter" class="btn-ajouter">Enregistrer</button>
                        </form>
                    </fieldset>
                </div>
                
            <?php else: ?>
                <p>Veuillez vous <a href="compte.php">connecter</a> pour accéder à cette page.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
// Libération des ressources
if (isset($stid_select)) {
    oci_free_statement($stid_select);
}
oci_close($idconn);
include("../includes/administrator-footer.php"); 
?>
</body>
</html>