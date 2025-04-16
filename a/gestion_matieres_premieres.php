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

// Traitement des ajouts et suppressions de matières premières
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'une matière première
    if (isset($_POST['ajouter_matiere']) && !empty($_POST['nom_matiere_premiere']) && !empty($_POST['id_matiere_premiere'])) {
        $nom_matiere_premiere = htmlspecialchars($_POST['nom_matiere_premiere']);
        $id_matiere_premiere = htmlspecialchars($_POST['id_matiere_premiere']);

        // Validation de l'ID (longueur maximale)
        if (strlen($id_matiere_premiere) > 8) {
            $_SESSION['message'] = "<p class='error'>L'ID de la matière première ne doit pas dépasser 8 caractères.</p>";
            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
            exit();
        }

        try {
            // Vérifier si la matière première existe déjà (par ID ou nom)
            $sql_check = "SELECT COUNT(*) AS count FROM les_matieres_premieres WHERE id_matiere_premiere = :id_matiere_premiere OR nom_matiere_premiere = :nom_matiere_premiere";
            $stid_check = oci_parse($idconn, $sql_check);
            oci_bind_by_name($stid_check, ':id_matiere_premiere', $id_matiere_premiere);
            oci_bind_by_name($stid_check, ':nom_matiere_premiere', $nom_matiere_premiere);
            oci_execute($stid_check);
            $row = oci_fetch_assoc($stid_check);

            if ($row['COUNT'] > 0) {
                $_SESSION['message'] = "<p class='error'>Cette matière première existe déjà (ID ou nom).</p>";
            } else {
                // Ajouter la matière première
                $sql_insert = "INSERT INTO les_matieres_premieres (id_matiere_premiere, nom_matiere_premiere) VALUES (:id_matiere_premiere, :nom_matiere_premiere)";
                $stid_insert = oci_parse($idconn, $sql_insert);
                oci_bind_by_name($stid_insert, ':id_matiere_premiere', $id_matiere_premiere);
                oci_bind_by_name($stid_insert, ':nom_matiere_premiere', $nom_matiere_premiere);

                if (oci_execute($stid_insert)) {
                    $_SESSION['message'] = "<p class='success'>Matière première ajoutée avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de l'ajout de la matière première.</p>";
                }
                oci_free_statement($stid_insert);
            }
            oci_free_statement($stid_check);
        } catch (Exception $e) {
            $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }

        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
        exit();
    }

    // Suppression d'une matière première
    if (isset($_POST['supprimer_matiere']) && !empty($_POST['id_matiere_premiere'])) {
        $id_matiere_premiere = htmlspecialchars($_POST['id_matiere_premiere']);

        try {
            // Supprimer la matière première
            $sql_delete = "DELETE FROM les_matieres_premieres WHERE id_matiere_premiere = :id_matiere_premiere";
            $stid_delete = oci_parse($idconn, $sql_delete);
            oci_bind_by_name($stid_delete, ':id_matiere_premiere', $id_matiere_premiere);

            if (oci_execute($stid_delete)) {
                $_SESSION['message'] = "<p class='success'>Matière première supprimée avec succès.</p>";
            } else {
                $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression de la matière première.</p>";
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
    <title>SantaLogistics - Gestion Matières Premières</title>
</head>

<body>
    <?php include("../includes/administrator-header.php"); ?>

    <main>
        <div id="gestion-matieres-premieres" class="home-container">
            <div class="inner1-home-container">
                <h2>Gestion des Matières Premières</h2>
                <div class="sep2"></div>

                <?php if (isset($_SESSION["user_id"])) : ?>
                    <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Matières
                        Premières !</p>

                    <!-- Affichage des messages de session -->
                    <?php if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    } ?>

                    <!-- Section Liste des Matières Premières -->
                    <div class="liste-matieres">
                        <h3>Liste des Matières Premières</h3>
                        <fieldset>
                            <?php
                            // Récupération de la liste des matières premières
                            $sql_select = "SELECT id_matiere_premiere, nom_matiere_premiere FROM les_matieres_premieres ORDER BY id_matiere_premiere";
                            $stid_select = oci_parse($idconn, $sql_select);

                            if (!oci_execute($stid_select)) {
                                $e = oci_error($stid_select);
                                echo "<p class='error'>Erreur lors de la récupération des matières premières: " . htmlspecialchars($e['message']) . "</p>";
                            } else {
                                // Vérification s'il y a des résultats
                                $numrows = oci_fetch_all($stid_select, $results);

                                if ($numrows > 0) {
                                    echo '<table class="table-style">';
                                    echo '<thead>
                                            <tr>
                                                <th>ID Matière Première</th>
                                                <th>Nom Matière Première</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>';
                                    echo '<tbody>';

                                    // Ré-exécuter la requête pour parcourir les résultats
                                    oci_execute($stid_select);
                                    while ($row = oci_fetch_array($stid_select, OCI_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['ID_MATIERE_PREMIERE']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['NOM_MATIERE_PREMIERE']) . '</td>';
                                        echo '<td class="actions-cell">';

                                        // Formulaire de suppression
                                        echo '<form method="post" class="inline-form" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cette matière première ?\');">';
                                        echo '<input type="hidden" name="id_matiere_premiere" value="' . htmlspecialchars($row['ID_MATIERE_PREMIERE']) . '">';
                                        echo '<button type="submit" name="supprimer_matiere" class="btn-supprimer">Supprimer</button>';
                                        echo '</form>';

                                        echo '</td>';
                                        echo '</tr>';
                                    }

                                    echo '</tbody>';
                                    echo '</table>';
                                } else {
                                    echo "<p class='error'>Aucune matière première trouvée dans la base de données.</p>";
                                }
                            }

                            oci_free_statement($stid_select);
                            ?>
                        </fieldset>
                    </div>

                    <!-- Section Ajout d'une nouvelle matière première -->
                    <div class="ajout-matiere">
                        <h3>Ajouter une nouvelle matière première</h3>
                        <fieldset>
                            <form method="post">
                                <div class="form-group">
                                    <label for="id_matiere_premiere">ID Matière Première (max. 8 caractères):</label>
                                    <input type="text" id="id_matiere_premiere" name="id_matiere_premiere" maxlength="8" required>
                                </div>
                                <div class="form-group">
                                    <label for="nom_matiere_premiere">Nom de la matière première :</label>
                                    <input type="text" id="nom_matiere_premiere" name="nom_matiere_premiere" required>
                                </div>
                                <button type="submit" name="ajouter_matiere" class="btn-ajouter">Ajouter</button>
                            </form>
                        </fieldset>
                    </div>

                <?php else : ?>
                    <p>Veuillez vous <a href="compte.php">connecter</a> pour accéder à cette page.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include("../includes/administrator-footer.php"); ?>
</body>

</html>
