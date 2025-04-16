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

// TRAITEMENT DE LA MODIFICATION D'UNE ÉQUIPE LOGISTIQUE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_equipe_logistique']) && isset($_POST['modifier'])) {
        // Récupération des données du formulaire
        $id_equipe_logistique = $_POST['id_equipe_logistique'];
        $id_entrepot = $_POST['id_entrepot'];
    
        // Préparation de la requête SQL de mise à jour
        $sql_update = "UPDATE les_equipes_logistiques SET id_entrepot = :id_entrepot WHERE id_equipe_logistique = :id_equipe_logistique";
        $stid_update = oci_parse($idconn, $sql_update);
    
        // Liaison des paramètres
        oci_bind_by_name($stid_update, ':id_entrepot', $id_entrepot);
        oci_bind_by_name($stid_update, ':id_equipe_logistique', $id_equipe_logistique);

        // Exécution de la requête
        if (oci_execute($stid_update)) {
            $_SESSION['message'] = "<p class='success'>Modification réussie pour l'équipe logistique portant ID : " . htmlspecialchars($id_equipe_logistique) . "</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        } else {
            $_SESSION['message'] = "<p class='error'>Erreur lors de la modification.</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        }
    
        oci_free_statement($stid_update);
    }
    // TRAITEMENT DE LA SUPPRESSION D'UNE ÉQUIPE LOGISTIQUE
    elseif (isset($_POST['id_equipe_logistique']) && isset($_POST['supprimer'])) {
        $id_equipe_logistique = $_POST['id_equipe_logistique'];
        
        try {
            // Vérifier s'il y a des associations avant suppression
            $sql_check = "SELECT COUNT(*) AS count FROM les_elfes WHERE id_equipe_logistique = :id_equipe_logistique";
            $stid_check = oci_parse($idconn, $sql_check);
            oci_bind_by_name($stid_check, ':id_equipe_logistique', $id_equipe_logistique);
            oci_execute($stid_check);
            $row = oci_fetch_assoc($stid_check);
            $count_associations = $row['COUNT'];
            oci_free_statement($stid_check);
            
            if ($count_associations > 0) {
                $_SESSION['message'] = "<p class='error'>Impossible de supprimer l'équipe car elle est associée à des lutins.</p>";
            } else {
                // Supprimer l'équipe
                $sql_delete = "DELETE FROM les_equipes_logistiques WHERE id_equipe_logistique = :id_equipe_logistique";
                $stid_delete = oci_parse($idconn, $sql_delete);
                oci_bind_by_name($stid_delete, ':id_equipe_logistique', $id_equipe_logistique);
                
                if (oci_execute($stid_delete)) {
                    $_SESSION['message'] = "<p class='success'>Équipe logistique supprimée avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression de l'équipe logistique.</p>";
                }
                oci_free_statement($stid_delete);
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }
    // TRAITEMENT DE L'AJOUT D'UNE NOUVELLE ÉQUIPE LOGISTIQUE
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
        // Récupérer les données du formulaire
        $id_equipe_logistique = $_POST['id_equipe_logistique'];
        $id_entrepot = $_POST['id_entrepot'];

        // Valider et sécuriser les données
        $id_equipe_logistique = htmlspecialchars($id_equipe_logistique);
        $id_entrepot = htmlspecialchars($id_entrepot);

        // Vérifier si l'ID équipe existe déjà
        $requete = "SELECT COUNT(*) AS COUNT FROM LES_EQUIPES_LOGISTIQUES WHERE ID_EQUIPE_LOGISTIQUE = :id_equipe_logistique";
        $stmt = oci_parse($idconn, $requete);
        oci_bind_by_name($stmt, ":id_equipe_logistique", $id_equipe_logistique);
        oci_execute($stmt);
        $result_check = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if ($result_check['COUNT'] == 0) {
            // Vérifier que l'entrepôt existe
            $requete_entrepot = "SELECT COUNT(*) AS COUNT FROM LES_ENTREPOTS WHERE ID_ENTREPOT = :id_entrepot";
            $stmt_entrepot = oci_parse($idconn, $requete_entrepot);
            oci_bind_by_name($stmt_entrepot, ":id_entrepot", $id_entrepot);
            oci_execute($stmt_entrepot);
            $result_entrepot = oci_fetch_assoc($stmt_entrepot);
            oci_free_statement($stmt_entrepot);

            if ($result_entrepot['COUNT'] > 0) {
                // Insérer les informations de l'équipe
                $requete_insertion = "INSERT INTO LES_EQUIPES_LOGISTIQUES (ID_EQUIPE_LOGISTIQUE, ID_ENTREPOT) VALUES (:id_equipe_logistique, :id_entrepot)";
                $stmt_insertion = oci_parse($idconn, $requete_insertion);

                oci_bind_by_name($stmt_insertion, ":id_equipe_logistique", $id_equipe_logistique);
                oci_bind_by_name($stmt_insertion, ":id_entrepot", $id_entrepot);

                if (!oci_execute($stmt_insertion)) {
                    $e = oci_error($stmt_insertion);
                    throw new Exception("Une erreur s'est produite lors de l'insertion de l'équipe logistique : " . $e['message']);
                }
                
                // Libération des ressources
                oci_free_statement($stmt_insertion);
                
                // Valider la transaction
                $validation = "commit";
                $stmtv = oci_parse($idconn, $validation);
                oci_execute($stmtv);
                oci_free_statement($stmtv);
            
                $_SESSION['message'] = "<div class='success'>L'équipe logistique a été ajoutée avec succès !</div>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            } else {
                $_SESSION['message'] = "<div class='error'>L'ID entrepôt spécifié n'existe pas !</div>";
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }
        } else {
            $_SESSION['message'] = "<div class='error'>L'ID équipe logistique existe déjà !</div>";
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
    <title>SantaLogistics - Gestion Équipes Logistiques</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

<main>
    <div id="gestion-equipes-logistiques" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Équipes Logistiques</h2>
            <div class="sep2"></div>
            
            <?php if (isset($_SESSION["user_id"])): ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Équipes Logistiques !</p>

                <!-- Affichage des messages de session -->
                <?php if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                } ?>

                <!-- Section Liste des Équipes Logistiques -->
                <div class="liste-equipes">
                    <h3>Liste des Équipes Logistiques</h3>
                    <fieldset>
                        <?php
                        // Récupération de la liste des entrepôts pour le select
                        $sql_entrepots = "SELECT id_entrepot, nom_region FROM les_entrepots ORDER BY id_entrepot";
                        $stid_entrepots = oci_parse($idconn, $sql_entrepots);
                        oci_execute($stid_entrepots);
                        $entrepots = [];
                        while ($row = oci_fetch_array($stid_entrepots, OCI_ASSOC)) {
                            $entrepots[$row['ID_ENTREPOT']] = $row['NOM_REGION'];
                        }
                        oci_free_statement($stid_entrepots);

                        // Récupération de la liste des équipes logistiques
                        $sql_select = "SELECT id_equipe_logistique, id_entrepot FROM les_equipes_logistiques ORDER BY id_equipe_logistique";
                        $stid_select = oci_parse($idconn, $sql_select);
                        
                        if (!oci_execute($stid_select)) {
                            $e = oci_error($stid_select);
                            echo "<p class='error'>Erreur lors de la récupération des équipes logistiques: " . htmlspecialchars($e['message']) . "</p>";
                        } else {
                            // Vérification s'il y a des résultats
                            $numrows = oci_fetch_all($stid_select, $results);
                            
                            if ($numrows > 0) {
                                echo '<table class="table-style">';
                                echo '<thead><tr>
                                        <th>ID Équipe Logistique</th>
                                        <th>Entrepôt (Région)</th>
                                        <th>Actions</th>
                                      </tr></thead>';
                                echo '<tbody>';
                                
                                // Ré-exécuter la requête pour parcourir les résultats
                                oci_execute($stid_select);
                                while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>'.htmlspecialchars($row['ID_EQUIPE_LOGISTIQUE']).'</td>';
                                    echo '<td>';
                                    echo '<form method="post" class="inline-form">';
                                    echo '<input type="hidden" name="id_equipe_logistique" value="'.htmlspecialchars($row['ID_EQUIPE_LOGISTIQUE']).'">';
                                    
                                    // Sélecteur pour l'entrepôt
                                    echo '<select name="id_entrepot" required>';
                                    echo '<option value="">Sélectionnez un entrepôt</option>';
                                    foreach ($entrepots as $id => $region) {
                                        $selected = ($id == $row['ID_ENTREPOT']) ? 'selected' : '';
                                        echo '<option value="'.htmlspecialchars($id).'" '.$selected.'>'.htmlspecialchars($id).' - '.htmlspecialchars($region).'</option>';
                                    }
                                    echo '</select>';
                                    echo '</td>';
                                    echo '<td class="actions-cell">';
                                    echo '<button type="submit" name="modifier" class="btn-modifier">Modifier</button>';
                                    echo '</form>';
                                    
                                    // Formulaire de suppression
                                    echo '<form method="post" class="inline-form" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cette équipe logistique?\');">';
                                    echo '<input type="hidden" name="id_equipe_logistique" value="'.htmlspecialchars($row['ID_EQUIPE_LOGISTIQUE']).'">';
                                    echo '<button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>';
                                    echo '</form>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "<p class='error'>Aucune équipe logistique trouvée dans la base de données.</p>";
                            }
                        }
                        ?>
                    </fieldset>
                    
                    <!-- Formulaire d'ajout d'une nouvelle équipe logistique -->
                    <h3>Ajouter une nouvelle équipe logistique</h3>
                    <fieldset>
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="id_equipe_logistique">ID Équipe Logistique:</label>
                                <input type="text" id="id_equipe_logistique" name="id_equipe_logistique" maxlength="8" placeholder="ID Équipe" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_entrepot">Entrepôt:</label>
                                <select id="id_entrepot" name="id_entrepot" required>
                                    <option value="">Sélectionnez un entrepôt</option>
                                    <?php
                                    foreach ($entrepots as $id => $region) {
                                        echo '<option value="'.htmlspecialchars($id).'">'.htmlspecialchars($id).' - '.htmlspecialchars($region).'</option>';
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