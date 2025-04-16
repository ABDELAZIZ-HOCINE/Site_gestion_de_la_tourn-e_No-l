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

// TRAITEMENT DE LA MODIFICATION D'UNE ÉQUIPE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_equipe_fabrication']) && isset($_POST['modifier'])) {
        // Récupération des données du formulaire
        $id_equipe_fabrication = $_POST['id_equipe_fabrication'];
        $id_atelier = $_POST['id_atelier'];
    
        // Préparation de la requête SQL de mise à jour
        $sql_update = "UPDATE les_equipes_fabrication SET id_atelier = :id_atelier WHERE id_equipe_fabrication = :id_equipe_fabrication";
        $stid_update = oci_parse($idconn, $sql_update);
    
        // Liaison des paramètres
        oci_bind_by_name($stid_update, ':id_atelier', $id_atelier);
        oci_bind_by_name($stid_update, ':id_equipe_fabrication', $id_equipe_fabrication);

        // Exécution de la requête
        if (oci_execute($stid_update)) {
            $_SESSION['message'] = "<p class='success'>Modification réussie pour l'équipe portant ID : " . htmlspecialchars($id_equipe_fabrication) . "</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        } else {
            $_SESSION['message'] = "<p class='error'>Erreur lors de la modification.</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        }
    
        oci_free_statement($stid_update);
    }
    // TRAITEMENT DE LA SUPPRESSION D'UNE ÉQUIPE
    elseif (isset($_POST['id_equipe_fabrication']) && isset($_POST['supprimer'])) {
        $id_equipe_fabrication = $_POST['id_equipe_fabrication'];
        
        try {
            // Vérifier s'il y a des associations avant suppression
            $sql_check = "SELECT COUNT(*) AS count FROM les_elfes WHERE id_equipe_fabrication = :id_equipe_fabrication";
            $stid_check = oci_parse($idconn, $sql_check);
            oci_bind_by_name($stid_check, ':id_equipe_fabrication', $id_equipe_fabrication);
            oci_execute($stid_check);
            $row = oci_fetch_assoc($stid_check);
            $count_associations = $row['COUNT'];
            oci_free_statement($stid_check);
            
            if ($count_associations > 0) {
                $_SESSION['message'] = "<p class='error'>Impossible de supprimer l'équipe car elle est associée à des lutins.</p>";
            } else {
                // Supprimer l'équipe
                $sql_delete = "DELETE FROM les_equipes_fabrication WHERE id_equipe_fabrication = :id_equipe_fabrication";
                $stid_delete = oci_parse($idconn, $sql_delete);
                oci_bind_by_name($stid_delete, ':id_equipe_fabrication', $id_equipe_fabrication);
                
                if (oci_execute($stid_delete)) {
                    $_SESSION['message'] = "<p class='success'>Équipe supprimée avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression de l'équipe.</p>";
                }
                oci_free_statement($stid_delete);
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }
    // TRAITEMENT DE L'AJOUT D'UNE NOUVELLE ÉQUIPE
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
        // Récupérer les données du formulaire
        $id_equipe_fabrication = $_POST['id_equipe_fabrication'];
        $id_atelier = $_POST['id_atelier'];

        // Valider et sécuriser les données
        $id_equipe_fabrication = htmlspecialchars($id_equipe_fabrication);
        $id_atelier = htmlspecialchars($id_atelier);

        // Vérifier si l'ID équipe existe déjà
        $requete = "SELECT COUNT(*) AS COUNT FROM LES_EQUIPES_FABRICATION WHERE ID_EQUIPE_FABRICATION = :id_equipe_fabrication";
        $stmt = oci_parse($idconn, $requete);
        oci_bind_by_name($stmt, ":id_equipe_fabrication", $id_equipe_fabrication);
        oci_execute($stmt);
        $result_check = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if ($result_check['COUNT'] == 0) {
            // Vérifier que l'atelier existe
            $requete_atelier = "SELECT COUNT(*) AS COUNT FROM LES_ATELIERS WHERE ID_ATELIER = :id_atelier";
            $stmt_atelier = oci_parse($idconn, $requete_atelier);
            oci_bind_by_name($stmt_atelier, ":id_atelier", $id_atelier);
            oci_execute($stmt_atelier);
            $result_atelier = oci_fetch_assoc($stmt_atelier);
            oci_free_statement($stmt_atelier);

            if ($result_atelier['COUNT'] > 0) {
                // Insérer les informations de l'équipe
                $requete_insertion = "INSERT INTO LES_EQUIPES_FABRICATION (ID_EQUIPE_FABRICATION, ID_ATELIER) VALUES (:id_equipe_fabrication, :id_atelier)";
                $stmt_insertion = oci_parse($idconn, $requete_insertion);

                oci_bind_by_name($stmt_insertion, ":id_equipe_fabrication", $id_equipe_fabrication);
                oci_bind_by_name($stmt_insertion, ":id_atelier", $id_atelier);

                if (!oci_execute($stmt_insertion)) {
                    $e = oci_error($stmt_insertion);
                    throw new Exception("Une erreur s'est produite lors de l'insertion de l'équipe : " . $e['message']);
                }
                
                // Libération des ressources
                oci_free_statement($stmt_insertion);
                
                // Valider la transaction
                $validation = "commit";
                $stmtv = oci_parse($idconn, $validation);
                oci_execute($stmtv);
                oci_free_statement($stmtv);
            
                $_SESSION['message'] = "<div class='success'>L'équipe a été ajoutée avec succès !</div>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            } else {
                $_SESSION['message'] = "<div class='error'>L'ID atelier spécifié n'existe pas !</div>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }
        } else {
            $_SESSION['message'] = "<div class='error'>L'ID équipe existe déjà !</div>";
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
    <title>SantaLogistics - Gestion Equipes Fabrication</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

<main>
    <div id="gestion-equipes-fabrication" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Équipes Fabrication</h2>
            <div class="sep2"></div>
            
            <?php if (isset($_SESSION["user_id"])): ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Équipes Fabrication !</p>

                <!-- Affichage des messages de session -->
                <?php if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                } ?>

                <!-- Section Liste des Équipes Fabrication -->
                <div class="liste-equipes">
                    <h3>Liste des Équipes Fabrication</h3>
                    <fieldset>
                        <?php
                        // Récupération de la liste des ateliers pour le select
                        $sql_ateliers = "SELECT id_atelier FROM les_ateliers ORDER BY id_atelier";
                        $stid_ateliers = oci_parse($idconn, $sql_ateliers);
                        oci_execute($stid_ateliers);
                        $ateliers = [];
                        while ($row = oci_fetch_array($stid_ateliers, OCI_ASSOC)) {
                            $ateliers[] = $row['ID_ATELIER'];
                        }
                        oci_free_statement($stid_ateliers);

                        // Récupération de la liste des équipes fabrication
                        $sql_select = "SELECT id_equipe_fabrication, id_atelier FROM les_equipes_fabrication ORDER BY id_equipe_fabrication";
                        $stid_select = oci_parse($idconn, $sql_select);
                        
                        if (!oci_execute($stid_select)) {
                            $e = oci_error($stid_select);
                            echo "<p class='error'>Erreur lors de la récupération des équipes fabrication: " . htmlspecialchars($e['message']) . "</p>";
                        } else {
                            // Vérification s'il y a des résultats
                            $numrows = oci_fetch_all($stid_select, $results);
                            
                            if ($numrows > 0) {
                                echo '<table class="table-style">';
                                echo '<thead><tr>
                                        <th>ID Équipe Fabrication</th>
                                        <th>ID Atelier</th>
                                        <th>Actions</th>
                                      </tr></thead>';
                                echo '<tbody>';
                                
                                // Ré-exécuter la requête pour parcourir les résultats
                                oci_execute($stid_select);
                                while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.htmlspecialchars($row['ID_EQUIPE_FABRICATION']).'</td>';
                                    echo '<td>';
                                    echo '<form method="post" class="inline-form">';
                                    echo '<input type="hidden" name="id_equipe_fabrication" value="'.htmlspecialchars($row['ID_EQUIPE_FABRICATION']).'">';
                                    
                                    // Sélecteur pour l'atelier
                                    echo '<select name="id_atelier" required>';
                                    foreach ($ateliers as $atelier) {
                                        $selected = ($atelier == $row['ID_ATELIER']) ? 'selected' : '';
                                        echo '<option value="'.htmlspecialchars($atelier).'" '.$selected.'>'.htmlspecialchars($atelier).'</option>';
                                    }
                                    echo '</select>';
                                    echo '</td>';
                                    echo '<td class="actions-cell">';
                                    echo '<button type="submit" name="modifier" class="btn-modifier">Modifier</button>';
                                    echo '</form>';
                                    
                                    // Formulaire de suppression
                                    echo '<form method="post" class="inline-form" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cette équipe?\');">';
                                    echo '<input type="hidden" name="id_equipe_fabrication" value="'.htmlspecialchars($row['ID_EQUIPE_FABRICATION']).'">';
                                    echo '<button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "<p class='error'>Aucune équipe fabrication trouvée dans la base de données.</p>";
                            }
                        }
                        ?>
                    </fieldset>
                    
                    <!-- Formulaire d'ajout d'une nouvelle équipe -->
                    <h3>Ajouter une nouvelle équipe</h3>
                    <fieldset>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="id_equipe_fabrication">ID Équipe Fabrication:</label>
                                <input type="text" id="id_equipe_fabrication" name="id_equipe_fabrication" maxlength="8" placeholder="ID Équipe" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_atelier">ID Atelier:</label>
                                <select id="id_atelier" name="id_atelier" required>
                                    <option value="">Sélectionnez un atelier</option>
                                    <?php
                                    foreach ($ateliers as $atelier) {
                                        echo '<option value="'.htmlspecialchars($atelier).'">'.htmlspecialchars($atelier).'</option>';
                                    }
                                    ?>
                                </select>
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