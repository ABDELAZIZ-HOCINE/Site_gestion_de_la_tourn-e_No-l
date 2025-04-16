<?php
    session_start();
    include_once("../connexion-base/connex.inc.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-windows.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-gest-rennes.css">
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics - Gestion Rennes</title>
</head>
<?php
    include("../includes/administrator-header.php");
?>

<main>
    <div id="gestion-rennes" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Rennes</h2>
            <div class="sep2"></div>
            <?php if (isset($_SESSION["user_id"])) { ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"] ?? '', ENT_QUOTES); ?></b> dans la gestion des rennes !</p>

                <div class="liste-rennes">
                    <h3>Liste des Rennes</h3>
                    
                    <fieldset>
                        <?php
                            // AFFICHAGE DES MESSAGES DE RETOUR
                            if (isset($_SESSION['message'] )){
                                echo $_SESSION['message'];
                                unset($_SESSION['message']); // Nettoyage du message après affichage
                            }
                        ?>
                        <?php
                            // Connexion à la base de données
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }

                            // Récupération des traîneaux existants
                            $sql_traineaux = "SELECT id_traineau, num_traineau FROM les_traineaux";
                            $stid_traineaux = oci_parse($idconn, $sql_traineaux);
                            oci_execute($stid_traineaux);
                            $traineaux = [];
                            while ($row = oci_fetch_array($stid_traineaux, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                $traineaux[] = $row;
                            }

                            // Récupération des rennes existants pour dirigeants
                            $sql_dirigeants = "SELECT id_renne, nom_renne FROM les_rennes";
                            $stid_dirigeants = oci_parse($idconn, $sql_dirigeants);
                            oci_execute($stid_dirigeants);
                            $dirigeants = [];
                            while ($row = oci_fetch_array($stid_dirigeants, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                $dirigeants[] = $row;
                            }

                            // Gestion des actions (modification ou suppression)
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                if (isset($_POST['id_renne']) && isset($_POST['modifier'])) {
                                    // Modification d'un renne
                                    $id_renne = $_POST['id_renne'];
                                    $nom_renne = $_POST['nom_renne'];
                                    $id_renne_dirigant = $_POST['id_renne_dirigant'] ?: null;
                                    $id_traineau = $_POST['id_traineau'];

                                    $sql_update = "UPDATE les_rennes SET nom_renne = :nom_renne, id_renne_dirigant = :id_renne_dirigant, id_traineau = :id_traineau WHERE id_renne = :id_renne";
                                    $stid_update = oci_parse($idconn, $sql_update);

                                    oci_bind_by_name($stid_update, ':nom_renne', $nom_renne);
                                    oci_bind_by_name($stid_update, ':id_renne_dirigant', $id_renne_dirigant);
                                    oci_bind_by_name($stid_update, ':id_traineau', $id_traineau);
                                    oci_bind_by_name($stid_update, ':id_renne', $id_renne);

                                    if (oci_execute($stid_update)) {
                                        $_SESSION['message'] = "<p class='success'>Modification réussie pour le renne portant ID : " . htmlspecialchars($id_renne) . "</p>";
                                        // Redirection pour éviter la resoumission
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    } else {
                                        $_SESSION['message'] =  "<p class='error'>Erreur lors de la modification.</p>";
                                        // Redirection pour éviter la resoumission
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    }
                                } elseif (isset($_POST['id_renne']) && isset($_POST['supprimer'])) {
                                    // Suppression d'un renne
                                    $id_renne = $_POST['id_renne'];

                                    $sql_delete = "DELETE FROM les_rennes WHERE id_renne = :id_renne";
                                    $stid_delete = oci_parse($idconn, $sql_delete);
                                    oci_bind_by_name($stid_delete, ':id_renne', $id_renne);

                                    if (oci_execute($stid_delete)) {
                                        $_SESSION['message'] =  "<p class='success'>Renne supprimé avec succès.</p>";
                                        // Redirection pour éviter la resoumission
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    } else {
                                        $_SESSION['message'] =  "<p class='error'>Erreur lors de la suppression.</p>";
                                        // Redirection pour éviter la resoumission
                                        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                        exit();
                                    }
                                }
                            }

                            // Récupération des données de la table les_rens
                            $sql_select = "SELECT * FROM les_rennes ORDER BY position_renne";
                            $stid_select = oci_parse($idconn, $sql_select);
                            oci_execute($stid_select);
                        ?>

                        <!-- Tableau affichant la liste des rennes -->
                        <table class="table-style">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Position</th>
                                    <th>ID Dirigeant</th>
                                    <th>ID Traîneau</th>
                                    <th>Nouveau nom</th>
                                    <th>Nouveau dirigant</th>
                                    <th>Nouveau trineau</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = oci_fetch_array($stid_select, OCI_ASSOC+OCI_RETURN_NULLS)) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ID_RENNE'] ?? '', ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['NOM_RENNE'] ?? '', ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars((string)$row['POSITION_RENNE'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['ID_RENNE_DIRIGANT'] ?? '', ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($row['ID_TRAINEAU'] ?? '', ENT_QUOTES) ?></td>
                                            <!-- Formulaire de modification -->
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="id_renne" value="<?= htmlspecialchars($row['ID_RENNE'] ?? '', ENT_QUOTES) ?>">
                                                <td>
                                                <input type="text" name="nom_renne" value="<?= htmlspecialchars($row['NOM_RENNE'] ?? '', ENT_QUOTES) ?>" required>
                                                </td>
                                                <!-- Liste déroulante pour les dirigeants -->
                                                <td>
                                                <select name="id_renne_dirigant">
                                                    <option value="">Aucun</option>
                                                    <?php foreach ($dirigeants as $dirigeant) { ?>
                                                        <option value="<?= htmlspecialchars($dirigeant['ID_RENNE'], ENT_QUOTES) ?>" <?= ($dirigeant['ID_RENNE'] === ($row['ID_RENNE_DIRIGANT'] ?? '')) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($dirigeant['NOM_RENNE'], ENT_QUOTES) ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                </td>
                                                <td>
                                                <!-- Liste déroulante pour les traîneaux -->
                                                <select name="id_traineau" required>
                                                    <?php foreach ($traineaux as $traineau) { ?>
                                                        <option value="<?= htmlspecialchars($traineau['ID_TRAINEAU'], ENT_QUOTES) ?>" <?= ($traineau['ID_TRAINEAU'] === ($row['ID_TRAINEAU'] ?? '')) ? 'selected' : '' ?>>
                                                            Traîneau #<?= htmlspecialchars((string)$traineau['NUM_TRAINEAU'], ENT_QUOTES) ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                </td>
                                                <td>
                                                <button type="submit" name="modifier" class="btn-modifier">Modifier</button>
                                                </td>
                                            </form>
                                            <td>
                                            <!-- Formulaire de suppression -->
                                            <form method="post" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce renne ?');">
                                                <input type="hidden" name="id_renne" value="<?= htmlspecialchars($row['ID_RENNE'] ?? '', ENT_QUOTES) ?>">
                                                <button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php
                        // Fonction pour exécuter une requête et retourner les résultats
                        function executeQuery($idconn, $sql, $params = []) {
                            $stid = oci_parse($idconn, $sql);
                            if (!$stid) {
                            $e = oci_error($idconn);
                            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
                            }
                            
                            foreach ($params as $key => $val) {
                            oci_bind_by_name($stid, $key, $params[$key]);
                            }
                            
                            if (!oci_execute($stid)) {
                            $e = oci_error($stid);
                            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
                            }
                            
                            return $stid;
                        }

                        // Récupérer la liste des dirigeants (rennes en position 1)
                        $sqlDirigeants = "SELECT ID_RENNE, NOM_RENNE FROM les_rennes WHERE POSITION_RENNE = 1";
                        $stid = executeQuery($idconn, $sqlDirigeants);
                        $dirigeants = [];
                        while ($row = oci_fetch_assoc($stid)) {
                            $dirigeants[] = $row;
                        }
                        oci_free_statement($stid);

                        // Récupérer la liste des traîneaux disponibles (avec moins de 8 rennes)
                        $sqlTraineaux = "
                            SELECT t.ID_TRAINEAU, t.NUM_TRAINEAU 
                            FROM les_traineaux t
                            LEFT JOIN les_rennes r ON t.ID_TRAINEAU = r.ID_TRAINEAU
                            GROUP BY t.ID_TRAINEAU, t.NUM_TRAINEAU
                            HAVING COUNT(r.ID_RENNE) < 8
                            ORDER BY t.NUM_TRAINEAU";
                        $stid = executeQuery($idconn, $sqlTraineaux);
                        $traineaux = [];
                        while ($row = oci_fetch_assoc($stid)) {
                            $traineaux[] = $row;
                        }
                        oci_free_statement($stid);
                        ?>
                        <?php
                            // Libération des ressources
                            oci_free_statement($stid_select);
                            oci_free_statement($stid_dirigeants);
                            oci_free_statement($stid_traineaux);
                            oci_close($idconn);
                        ?>
                    </fieldset>
                </div>

                <div class="ajout-renne">
                    <h3>Ajouter une Renne</h3>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <label for="id_traineau_ajout">Traîneau :</label>
                        <select name="id_traineau_ajout" id="id_traineau_ajout" required>
                            <?php
                            // Connexion à la base de données
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }
                            // Requête pour récupérer les traîneaux
                            $sql_traineaux = "SELECT id_traineau, num_traineau FROM les_traineaux";
                            $stid_traineaux = oci_parse($idconn, $sql_traineaux);
                            oci_execute($stid_traineaux);

                            while ($row = oci_fetch_array($stid_traineaux, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                echo '<option value="' . htmlspecialchars($row["ID_TRAINEAU"], ENT_QUOTES) . '">' . htmlspecialchars($row["NUM_TRAINEAU"] . ' - ' . $row["ID_TRAINEAU"], ENT_QUOTES) . '</option>';
                            }

                            oci_free_statement($stid_traineaux);
                            oci_close($idconn);
                            ?>
                        </select><br>

                        <label for="id_renne">ID de la Renne :</label>
                        <input type="text" name="id_renne" id="id_renne" maxlength="8" required><br>

                        <label for="nom_renne_ajout">Nom de la Renne :</label>
                        <input type="text" name="nom_renne_ajout" id="nom_renne_ajout" maxlength="50" required><br>

                        <label for="id_renne_dirigant">Id renne dirigant :</label>
                        <select name="id_renne_dirigant" id="id_renne_dirigant" required>
                            <?php
                            // Connexion à la base de données
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }
                            // Requête pour récupérer les traîneaux
                            $sql_rennes = "SELECT id_renne FROM les_rennes";
                            $stid_rennes = oci_parse($idconn, $sql_rennes);
                            oci_execute($stid_rennes);

                            while ($row = oci_fetch_array($stid_rennes, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                echo '<option value="' . htmlspecialchars($row["ID_RENNE"], ENT_QUOTES) . '">' . htmlspecialchars($row["ID_RENNE"], ENT_QUOTES) . '</option>';
                            }

                            oci_free_statement($stid_rennes);
                            oci_close($idconn);
                            ?>
                        </select><br>

                        <label for="position_renne_ajout">Position : (1-8)</label>

                        <select name="position_renne_ajout" id="position_renne_ajout" required>
                            <?php for ($i = 1; $i <= 8; $i++){ ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select><br>

                        <input type="submit" name="ajouter_renne" value="Ajouter Renne">
                    </form>
                </div>

                <?php
                    // Fonction pour récupérer les positions occupées dans un traîneau
                    function getOccupiedPositions($idconn, $id_traineau) {
                        $sql = "SELECT position_renne FROM les_rennes WHERE id_traineau = :id_traineau";
                        $stmt = oci_parse($idconn, $sql);
                        oci_bind_by_name($stmt, ':id_traineau', $id_traineau);
                        oci_execute($stmt);

                        $occupiedPositions = [];
                        while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
                            $occupiedPositions[] = $row['POSITION_RENNE'];
                        }
                        return $occupiedPositions;
                    }
                    function IfExistId($id, $table) {
                        $idconn = connexoci("my-param", "oracle2");
                        if (!$idconn) {
                            throw new Exception("Erreur de connexion à la base de données");
                        }
                    
                        $sql = "SELECT COUNT(*) AS COUNT FROM $table WHERE id_renne = :id";
                        $stmt = oci_parse($idconn, $sql);
                        
                        if (!$stmt) {
                            oci_close($idconn);
                            throw new Exception("Erreur de préparation de requête");
                        }
                        
                        oci_bind_by_name($stmt, ":id", $id);
                        
                        if (!oci_execute($stmt)) {
                            oci_free_statement($stmt);
                            oci_close($idconn);
                            throw new Exception("Erreur d'exécution de requête");
                        }
                        
                        $row = oci_fetch_assoc($stmt);
                        $exists = ($row['COUNT'] > 0);
                        
                        oci_free_statement($stmt);
                        oci_close($idconn);
                        
                        return $exists;
                    }
                    // Gestion de l'ajout d'une renne
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_renne'])) {
                        $id_traineau = $_POST['id_traineau_ajout']??'';
                        $id_renne_dirigant = $_POST['id_renne_dirigant'];
                        $nom_renne = $_POST['nom_renne_ajout']??'';
                        $position_renne = $_POST['position_renne_ajout'];
                        $id_renne = $_POST['id_renne'];

                        // Vérifier si la position est déjà occupée
                        $idconn = connexoci("my-param", "oracle2");
                        if (!$idconn) {
                            $e = oci_error();
                            throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                        }
                        $occupiedPositions = getOccupiedPositions($idconn, $id_traineau);
                        oci_close($idconn);
                        
                        if (IfExistId($id_renne, "les_rennes") || $id_traineau == '' ) {
                            $_SESSION['message'] = "<p class='error'>Erreur : La ID renne : " . htmlspecialchars($id_renne) . " est déjà utilisé !.</p>";
                            exit();
                        }
                        if (in_array($position_renne, $occupiedPositions)) {
                            $_SESSION['message'] = "<p class='error'>Erreur : La position " . htmlspecialchars($position_renne) . " est déjà occupée dans ce traîneau.</p>";
                        }else {
                            // Ajouter la renne à la base de données
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }

                            $sql_insert = "INSERT INTO les_rennes (id_renne, nom_renne, position_renne, id_renne_dirigant, id_traineau) VALUES (:id_renne, :nom_renne, :position_renne, :id_renne_dirigant, :id_traineau)";
                            $stmt_insert = oci_parse($idconn, $sql_insert);
                            oci_bind_by_name($stmt_insert, ':id_renne', $id_renne);
                            oci_bind_by_name($stmt_insert, ':nom_renne', $nom_renne);
                            oci_bind_by_name($stmt_insert, ':position_renne', $position_renne);
                            oci_bind_by_name($stmt_insert, ':id_traineau', $id_traineau);
                            oci_bind_by_name($stmt_insert, ':id_renne_dirigant', $id_renne_dirigant);

                            $result = oci_execute($stmt_insert);
                            if ($result) {
                                oci_commit($idconn);
                                $_SESSION['message'] = "<p class='success'>La renne " . htmlspecialchars($nom_renne) . " a été ajoutée avec succès à la position " . htmlspecialchars($position_renne) . " du traîneau " . htmlspecialchars($id_traineau) . ".</p>";
                                // Redirection pour éviter la resoumission
                                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                exit();
                            } else {
                                $e = oci_error($stmt_insert);
                                $_SESSION['message'] = "<p class='error'>Erreur lors de l'ajout de la renne : " . htmlspecialchars($e['message']) . "</p>";
                                // Redirection pour éviter la resoumission
                                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                exit();
                            }
                            oci_free_statement($stmt_insert);
                            oci_close($idconn);
                        }
                    }

                ?>
            <?php } else { ?>
                <p>Veuillez vous connecter pour accéder à la gestion des rennes.</p>
            <?php } ?>
        </div>
    </div>
</main>

<?php
    include("../includes/administrator-footer.php");
?>
