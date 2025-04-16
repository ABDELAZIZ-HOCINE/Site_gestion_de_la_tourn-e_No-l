<?php
    // Démarrage de la session pour gérer les variables de session
    session_start();
    // Inclusion du fichier de connexion à la base de données
    include_once("../connexion-base/connex.inc.php");   
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Métadonnées de base -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Feuilles de style -->
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-windows.css">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics - Gestion Entrepôts</title>
</head>
<?php
    // Inclusion de l'en-tête administrateur
    include("../includes/administrator-header.php");
?>

<main>
    <div id="gestion-entrepots" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Entrepôts</h2>
            <div class="sep2"></div>
            <?php if (isset($_SESSION["user_id"])){ ?>
                <!-- Message de bienvenue avec le nom de l'utilisateur connecté -->
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des entrepôts !</p>

                <!-- Section Liste des Entrepôts -->
                <div class="liste-entrepots">
                    <h3>Liste des Entrepôts</h3>
                    
                    <fieldset>
                        <?php
                            // Affichage des messages de session
                            if (isset($_SESSION['message'])) {
                                echo $_SESSION['message'];
                                unset($_SESSION['message']);
                            }
                        ?>
                        <?php
                            // CONNEXION À LA BASE DE DONNÉES ORACLE
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }
                            
                            // TRAITEMENT DE LA MODIFICATION D'UN ENTREPOT
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                if (isset($_POST['id_entrepot']) && isset($_POST['modifier'])) {
                                    // Récupération des données du formulaire
                                    $id_entrepot = $_POST['id_entrepot'];
                                    $nom_region = $_POST['nom_region'];
                                
                                    // Préparation de la requête SQL de mise à jour
                                    $sql_update = "UPDATE les_entrepots SET nom_region = :nom_region WHERE id_entrepot = :id_entrepot";
                                    $stid_update = oci_parse($idconn, $sql_update);
                                
                                    // Liaison des paramètres
                                    oci_bind_by_name($stid_update, ':nom_region', $nom_region);
                                    oci_bind_by_name($stid_update, ':id_entrepot', $id_entrepot);

                                    // Exécution de la requête
                                    if (oci_execute($stid_update)) {
                                        $_SESSION['message'] = "<p class='success'>Modification réussie pour l'entrepôt portant ID : " . htmlspecialchars($id_entrepot) . "</p>";
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    } else {
                                        $_SESSION['message'] = "<p class='error'>Erreur lors de la modification.</p>";
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    }
                                
                                    oci_free_statement($stid_update);
                                }
                                // TRAITEMENT DE LA SUPPRESSION D'UN ENTREPOT
                                elseif (isset($_POST['id_entrepot']) && isset($_POST['supprimer'])) {
                                    $id_entrepot = $_POST['id_entrepot'];
                                    
                                    try {
                                        // Vérifier s'il y a des équipes logistiques ou des traîneaux associés
                                        $sql_check = "SELECT COUNT(*) AS count FROM les_equipes_logistiques WHERE id_entrepot = :id_entrepot";
                                        $stid_check = oci_parse($idconn, $sql_check);
                                        oci_bind_by_name($stid_check, ':id_entrepot', $id_entrepot);
                                        oci_execute($stid_check);
                                        $row = oci_fetch_assoc($stid_check);
                                        $count_equipes = $row['COUNT'];
                                        oci_free_statement($stid_check);
                                        
                                        $sql_check = "SELECT COUNT(*) AS count FROM les_traineaux WHERE id_entrepot = :id_entrepot";
                                        $stid_check = oci_parse($idconn, $sql_check);
                                        oci_bind_by_name($stid_check, ':id_entrepot', $id_entrepot);
                                        oci_execute($stid_check);
                                        $row = oci_fetch_assoc($stid_check);
                                        $count_traineaux = $row['COUNT'];
                                        oci_free_statement($stid_check);
                                        
                                        if ($count_equipes > 0 || $count_traineaux > 0) {
                                            $_SESSION['message'] = "<p class='error'>Impossible de supprimer l'entrepôt car il est associé à des équipes logistiques ou des traîneaux.</p>";
                                        } else {
                                            // Supprimer l'entrepôt
                                            $sql_delete = "DELETE FROM les_entrepots WHERE id_entrepot = :id_entrepot";
                                            $stid_delete = oci_parse($idconn, $sql_delete);
                                            oci_bind_by_name($stid_delete, ':id_entrepot', $id_entrepot);
                                            
                                            if (oci_execute($stid_delete)) {
                                                $_SESSION['message'] = "<p class='success'>Entrepôt supprimé avec succès.</p>";
                                            } else {
                                                $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression de l'entrepôt.</p>";
                                            }
                                            oci_free_statement($stid_delete);
                                        }
                                    } catch (Exception $e) {
                                        $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
                                    }
                                    
                                    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                    exit();
                                }
                            }
                            
                            // RÉCUPÉRATION DE LA LISTE DES ENTREPOTS
                            $sql_select = "SELECT * FROM les_entrepots ORDER BY nom_region";
                            $stid_select = oci_parse($idconn, $sql_select);
                            oci_execute($stid_select);     
                        ?>

                        <!-- TABLEAU AFFICHANT LA LISTE DES ENTREPOTS -->
                        <h1>Liste des Entrepôts</h1>
                        <table class="table-style">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom de la région</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = oci_fetch_array($stid_select, OCI_ASSOC+OCI_RETURN_NULLS)){ ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ID_ENTREPOT']) ?></td>
                                        <td><?= htmlspecialchars($row['NOM_REGION']) ?></td>
                                        <td class="actions-cell">
                                            <!-- Formulaire de modification -->
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="id_entrepot" value="<?= htmlspecialchars($row['ID_ENTREPOT']) ?>">
                                                <input type="text" name="nom_region" value="<?= htmlspecialchars($row['NOM_REGION']) ?>" required>
                                                <button type="submit" name="modifier" class="btn-modifier">Modifier</button>
                                            </form>
                                            
                                            <!-- Formulaire de suppression -->
                                            <form method="post" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet entrepôt?');">
                                                <input type="hidden" name="id_entrepot" value="<?= htmlspecialchars($row['ID_ENTREPOT']) ?>">
                                                <button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Formulaire d'ajout d'un nouvel entrepôt -->
                        <h2>Ajouter un entrepôt:</h2>
                        <fieldset>
                            <form method="post" action="">
                                <!-- Champ pour l'identifiant de l'entrepôt -->
                                <label for="id_entrepot">ID Entrepôt:</label>
                                <input type="text" id="id_entrepot" name="id_entrepot" maxlength="8" placeholder="ID Entrepôt" required>

                                <!-- Champ pour le nom de la région -->
                                <label for="nom_region">Nom de la région:</label>
                                <input type="text" id="nom_region" name="nom_region" maxlength="50" placeholder="Nom de la région" required>

                                <button type="submit" name="envoyer">Ajouter</button>
                            </form>
                        </fieldset>
                        
                        <?php
                            // Traitement du formulaire soumis
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['envoyer'])) {
                                // Récupérer les données du formulaire
                                $id_entrepot = $_POST['id_entrepot'];
                                $nom_region = $_POST['nom_region'];

                                // Valider et sécuriser les données
                                $id_entrepot = htmlspecialchars($id_entrepot);
                                $nom_region = htmlspecialchars($nom_region);

                                // Vérifier si l'ID entrepôt existe déjà
                                $requete = "SELECT COUNT(*) AS COUNT FROM LES_ENTREPOTS WHERE ID_ENTREPOT = :id_entrepot";
                                $stmt = oci_parse($idconn, $requete);
                                oci_bind_by_name($stmt, ":id_entrepot", $id_entrepot);
                                oci_execute($stmt);
                                $result_check = oci_fetch_assoc($stmt);
                                oci_free_statement($stmt);

                                if ($result_check['COUNT'] == 0) {
                                    // Insérer les informations de l'entrepôt
                                    $requete_insertion = "INSERT INTO LES_ENTREPOTS (ID_ENTREPOT, NOM_REGION) VALUES (:id_entrepot, :nom_region)";
                                    $stmt_insertion = oci_parse($idconn, $requete_insertion);

                                    oci_bind_by_name($stmt_insertion, ":id_entrepot", $id_entrepot);
                                    oci_bind_by_name($stmt_insertion, ":nom_region", $nom_region);

                                    if (!oci_execute($stmt_insertion)) {
                                        $e = oci_error($stmt_insertion);
                                        throw new Exception("Une erreur s'est produite lors de l'insertion de l'entrepôt : " . $e['message']);
                                    }
                                    
                                    // Libération des ressources
                                    oci_free_statement($stmt_insertion);
                                    
                                    // Valider la transaction
                                    $validation = "commit";
                                    $stmtv = oci_parse($idconn, $validation);
                                    oci_execute($stmtv);
                                    oci_free_statement($stmtv);
                                
                                    $_SESSION['message'] = "<div class='success'>L'entrepôt a été ajouté avec succès !</div>";
                                    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                    exit();
                                } else {
                                    $_SESSION['message'] = "<div class='error'>L'ID entrepôt existe déjà !</div>";
                                    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                    exit();
                                }
                            }
                        ?>
                        <?php
                            // Libération des ressources
                            oci_free_statement($stid_select);
                            oci_close($idconn);
                        ?>
                    </fieldset>
                </div>

            <?php }else{ ?>
                <!-- MESSAGE SI UTILISATEUR NON CONNECTÉ -->
                <p>Veuillez vous connecter pour accéder à la gestion des entrepôts.</p>               
                <p><a href="compte.php">Se connecter</a></p>
            <?php } ?>
        </div>
    </div>
</main>

<?php
    // Inclusion du pied de page administrateur
    include("../includes/administrator-footer.php");
?>
