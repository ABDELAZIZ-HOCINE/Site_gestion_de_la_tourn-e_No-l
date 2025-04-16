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

// TRAITEMENT DE LA MODIFICATION D'UN ATELIER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_atelier']) && isset($_POST['modifier'])) {
        // Récupération des données du formulaire
        $id_atelier = $_POST['id_atelier'];
        $num_atelier = $_POST['num_atelier'];
        $activite = $_POST['activite'];
    
        // Préparation de la requête SQL de mise à jour
        $sql_update = "UPDATE les_ateliers SET num_atelier = :num_atelier, activite = :activite WHERE id_atelier = :id_atelier";
        $stid_update = oci_parse($idconn, $sql_update);
    
        // Liaison des paramètres
        oci_bind_by_name($stid_update, ':num_atelier', $num_atelier);
        oci_bind_by_name($stid_update, ':activite', $activite);
        oci_bind_by_name($stid_update, ':id_atelier', $id_atelier);

        // Exécution de la requête
        if (oci_execute($stid_update)) {
            $_SESSION['message'] = "<p class='success'>Modification réussie pour l'atelier portant ID : " . htmlspecialchars($id_atelier) . "</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        } else {
            $_SESSION['message'] = "<p class='error'>Erreur lors de la modification.</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        }
    
        oci_free_statement($stid_update);
    }
    // TRAITEMENT DE LA SUPPRESSION D'UN ATELIER
    elseif (isset($_POST['id_atelier']) && isset($_POST['supprimer'])) {
        $id_atelier = $_POST['id_atelier'];
        
        try {
            // Vérifier s'il y a des associations avant suppression
            $sql_check = "SELECT COUNT(*) AS COUNT FROM passe_par WHERE id_atelier LIKE :id_atelier";
            $stid_check = oci_parse($idconn, $sql_check);
            oci_bind_by_name($stid_check, ':id_atelier', $id_atelier);
            oci_execute($stid_check);
            $row = oci_fetch_assoc($stid_check);
            $count_associations = $row['COUNT'];
            oci_free_statement($stid_check);
            
            if ($count_associations > 0) {
                $_SESSION['message'] = "<p class='error'>Impossible de supprimer l'atelier car il est associé à des cadeaux.</p>";
            } else {
                // Supprimer l'atelier
                $sql_delete = "DELETE FROM les_ateliers WHERE id_atelier = :id_atelier";
                $stid_delete = oci_parse($idconn, $sql_delete);
                oci_bind_by_name($stid_delete, ':id_atelier', $id_atelier);
                
                if (oci_execute($stid_delete)) {
                    $_SESSION['message'] = "<p class='success'>Atelier supprimé avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression de l'atelier.</p>";
                }
                oci_free_statement($stid_delete);
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }
    // TRAITEMENT DE L'AJOUT D'UN NOUVEL ATELIER
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
        // Récupérer les données du formulaire
        $id_atelier = $_POST['id_atelier'];
        $num_atelier = $_POST['num_atelier'];
        $activite = $_POST['activite'];

        // Valider et sécuriser les données
        $id_atelier = htmlspecialchars($id_atelier);
        $num_atelier = htmlspecialchars($num_atelier);
        $activite = htmlspecialchars($activite);

        // Vérifier si l'ID atelier existe déjà
        $requete = "SELECT COUNT(*) AS COUNT FROM LES_ATELIERS WHERE ID_ATELIER = :id_atelier";
        $stmt = oci_parse($idconn, $requete);
        oci_bind_by_name($stmt, ":id_atelier", $id_atelier);
        oci_execute($stmt);
        $result_check = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if ($result_check['COUNT'] == 0) {
            // Insérer les informations de l'atelier
            $requete_insertion = "INSERT INTO LES_ATELIERS (ID_ATELIER, NUM_ATELIER, ACTIVITE) VALUES (:id_atelier, :num_atelier, :activite)";
            $stmt_insertion = oci_parse($idconn, $requete_insertion);

            oci_bind_by_name($stmt_insertion, ":id_atelier", $id_atelier);
            oci_bind_by_name($stmt_insertion, ":num_atelier", $num_atelier);
            oci_bind_by_name($stmt_insertion, ":activite", $activite);

            if (!oci_execute($stmt_insertion)) {
                $e = oci_error($stmt_insertion);
                throw new Exception("Une erreur s'est produite lors de l'insertion de l'atelier : " . $e['message']);
            }
            
            // Libération des ressources
            oci_free_statement($stmt_insertion);
            
            // Valider la transaction
            $validation = "commit";
            $stmtv = oci_parse($idconn, $validation);
            oci_execute($stmtv);
            oci_free_statement($stmtv);
        
            $_SESSION['message'] = "<div class='success'>L'atelier a été ajouté avec succès !</div>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        } else {
            $_SESSION['message'] = "<div class='error'>L'ID atelier existe déjà !</div>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        }
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
    <title>SantaLogistics - Gestion Ateliers</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

<main>
    <div id="gestion-ateliers" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Ateliers</h2>
            <div class="sep2"></div>
            
            <?php if (isset($_SESSION["user_id"])): ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Ateliers !</p>

                <!-- Affichage des messages de session -->
                <?php if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                } ?>

                <!-- Section Liste des Ateliers -->
                <div class="liste-ateliers">
                    <h3>Liste des Ateliers</h3>
                    <fieldset>
                        <?php
                        // Récupération de la liste des ateliers
                        $sql_select = "SELECT id_atelier, num_atelier, activite FROM les_ateliers ORDER BY id_atelier";
                        $stid_select = oci_parse($idconn, $sql_select);
                        
                        if (!oci_execute($stid_select)) {
                            $e = oci_error($stid_select);
                            echo "<p class='error'>Erreur lors de la récupération des ateliers: " . htmlspecialchars($e['message']) . "</p>";
                        } else {
                            // Vérification s'il y a des résultats
                            $numrows = oci_fetch_all($stid_select, $results);
                            
                            if ($numrows > 0) {
                                echo '<table class="table-style">';
                                echo '<thead><tr>
                                        <th>ID Atelier</th>
                                        <th>Numéro Atelier</th>
                                        <th>Activité</th>
                                        <th>Actions</th>
                                      </tr></thead>';
                                echo '<tbody>';
                                
                                // Ré-exécuter la requête pour parcourir les résultats
                                oci_execute($stid_select);
                                while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.htmlspecialchars($row['ID_ATELIER']).'</td>';
                                    echo '<td>';
                                    echo '<form method="post" class="inline-form">';
                                    echo '<input type="hidden" name="id_atelier" value="'.htmlspecialchars($row['ID_ATELIER']).'">';
                                    echo '<input type="text" name="num_atelier" value="'.htmlspecialchars($row['NUM_ATELIER']).'" required>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<input type="text" name="activite" value="'.htmlspecialchars($row['ACTIVITE']).'" required>';
                                    echo '</td>';
                                    echo '<td class="actions-cell">';
                                    echo '<button type="submit" name="modifier" class="btn-modifier">Modifier</button>';
                                    echo '</form>';
                                    
                                    // Formulaire de suppression
                                    echo '<form method="post" class="inline-form" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cet atelier?\');">';
                                    echo '<input type="hidden" name="id_atelier" value="'.htmlspecialchars($row['ID_ATELIER']).'">';
                                    echo '<button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "<p class='error'>Aucun atelier trouvé dans la base de données.</p>";
                            }
                        }
                        ?>
                    </fieldset>
                    
                    <!-- Formulaire d'ajout d'un nouvel atelier -->
                    <h3>Ajouter un nouvel atelier</h3>
                    <fieldset>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="id_atelier">ID Atelier:</label>
                                <input type="text" id="id_atelier" name="id_atelier" maxlength="8" placeholder="ID Atelier" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="num_atelier">Numéro Atelier:</label>
                                <input type="text" id="num_atelier" name="num_atelier" maxlength="20" placeholder="Numéro Atelier" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="activite">Activité:</label>
                                <input type="text" id="activite" name="activite" maxlength="100" placeholder="Activité" required>
                            </div>
                            
                            <button type="submit" name="ajouter" class="btn-ajouter">Ajouter</button>
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