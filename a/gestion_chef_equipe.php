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

// TRAITEMENT DES FORMULAIRES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_elfe_chef']) && isset($_POST['id_equipe_fabrication'])) {
        $id_elfe_chef = htmlspecialchars($_POST['id_elfe_chef']);
        $id_equipe_fabrication = htmlspecialchars($_POST['id_equipe_fabrication']);

        if (isset($_POST['ajouter'])) {
            // Ajouter un chef d'équipe
            try {
                // Vérifier si l'elfe est déjà chef d'une autre équipe
                $sql_check_elfe = "SELECT COUNT(*) AS count FROM chef_d_equipe WHERE id_elfe_chef = :id_elfe_chef";
                $stid_check_elfe = oci_parse($idconn, $sql_check_elfe);
                oci_bind_by_name($stid_check_elfe, ':id_elfe_chef', $id_elfe_chef);
                oci_execute($stid_check_elfe);
                $row = oci_fetch_assoc($stid_check_elfe);
                if ($row['COUNT'] > 0) {
                    $_SESSION['message'] = "<p class='error'>Cet elfe est déjà chef d'une autre équipe.</p>";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                // Vérifier si l'équipe a déjà un chef
                $sql_check_equipe = "SELECT COUNT(*) AS count FROM chef_d_equipe WHERE id_equipe_fabrication = :id_equipe_fabrication";
                $stid_check_equipe = oci_parse($idconn, $sql_check_equipe);
                oci_bind_by_name($stid_check_equipe, ':id_equipe_fabrication', $id_equipe_fabrication);
                oci_execute($stid_check_equipe);
                $row = oci_fetch_assoc($stid_check_equipe);
                if ($row['COUNT'] > 0) {
                    $_SESSION['message'] = "<p class='error'>Cette équipe a déjà un chef.</p>";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                // Ajouter le chef d'équipe
                $sql_insert = "INSERT INTO chef_d_equipe (id_elfe_chef, id_equipe_fabrication) VALUES (:id_elfe_chef, :id_equipe_fabrication)";
                $stid_insert = oci_parse($idconn, $sql_insert);
                oci_bind_by_name($stid_insert, ':id_elfe_chef', $id_elfe_chef);
                oci_bind_by_name($stid_insert, ':id_equipe_fabrication', $id_equipe_fabrication);

                if (oci_execute($stid_insert)) {
                    $_SESSION['message'] = "<p class='success'>Chef d'équipe ajouté avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de l'ajout du chef d'équipe.</p>";
                }
            } catch (Exception $e) {
                $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } elseif (isset($_POST['supprimer'])) {
            // Supprimer un chef d'équipe
            try {
                $sql_delete = "DELETE FROM chef_d_equipe WHERE id_elfe_chef = :id_elfe_chef AND id_equipe_fabrication = :id_equipe_fabrication";
                $stid_delete = oci_parse($idconn, $sql_delete);
                oci_bind_by_name($stid_delete, ':id_elfe_chef', $id_elfe_chef);
                oci_bind_by_name($stid_delete, ':id_equipe_fabrication', $id_equipe_fabrication);

                if (oci_execute($stid_delete)) {
                    $_SESSION['message'] = "<p class='success'>Chef d'équipe supprimé avec succès.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>Erreur lors de la suppression du chef d'équipe.</p>";
                }
            } catch (Exception $e) {
                $_SESSION['message'] = "<p class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
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
    <title>SantaLogistics - Gestion Chef d'Équipe</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

    <main>
    <div id="gestion-chefs-equipe" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Chefs d'Équipe</h2>

            <!-- Affichage des messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Liste des Chefs -->
            <h3>Liste des Chefs d'Équipe</h3>
            <fieldset>
                <?php
                // Récupération des chefs d'équipe
                $sql_select = "SELECT c.id_elfe_chef, e.nom AS nom_elfe, c.id_equipe_fabrication 
                               FROM chef_d_equipe c 
                               JOIN les_elfes e ON c.id_elfe_chef = e.id_elfe";
                $stid_select = oci_parse($idconn, $sql_select);
                
                if (!oci_execute($stid_select)) {
                    echo "<p class='error'>Erreur lors de la récupération des chefs d'équipe.</p>";
                } else {
                    echo '<table>';
                    echo '<tr><th>ID Équipe</th><th>Chef</th><th>Actions</th></tr>';
                    while ($row = oci_fetch_assoc($stid_select)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['ID_EQUIPE_FABRICATION']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['NOM_ELFE']) . '</td>';
                        echo '<td>';
                        echo '<form method="post">';
                        echo '<input type="hidden" name="id_elfe_chef" value="' . htmlspecialchars($row['ID_ELFE_CHEF']) . '">';
                        echo '<input type="hidden" name="id_equipe_fabrication" value="' . htmlspecialchars($row['ID_EQUIPE_FABRICATION']) . '">';
                        echo '<button type="submit" name="supprimer">Supprimer</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                ?>
            </fieldset>

            <!-- Formulaire pour ajouter un Chef -->
            <h3>Ajouter un nouveau Chef</h3>
            <form method="post">
                <label for="id_equipe_fabrication">Équipe :</label>
                <select name="id_equipe_fabrication" required>
                    <!-- Options dynamiques pour les équipes -->
                    <?php
                    // Récupération des équipes disponibles
                    $sql_equipes = "SELECT id_equipe_fabrication FROM les_equipes_fabrication";
                    $stid_equipes = oci_parse($idconn, $sql_equipes);
                    oci_execute($stid_equipes);
                    while ($row = oci_fetch_assoc($stid_equipes)) {
                        echo '<option value="' . htmlspecialchars($row['ID_EQUIPE_FABRICATION']) . '">' . htmlspecialchars($row['ID_EQUIPE_FABRICATION']) . '</option>';
                    }
                    ?>
                </select>

                <label for="id_elfe_chef">Elfe :</label>
                <select name="id_elfe_chef" required>
                    <!-- Options dynamiques pour les elfes -->
                    <?php
                    // Récupération des elfes disponibles
                    $sql_elfes = "SELECT id_elfe, nom FROM les_elfes";
                    $stid_elfes = oci_parse($idconn, $sql_elfes);
                    oci_execute($stid_elfes);
                    while ($row = oci_fetch_assoc($stid_elfes)) {
                        echo '<option value="' . htmlspecialchars($row['ID_ELFE']) . '">' . htmlspecialchars($row['NOM']) . '</option>';
                    }
                    ?>
                </select>

                <button type="submit" name="ajouter">Ajouter</button>
            </form>
        </div>
    </div>
</main>

<?php include("../includes/administrator-footer.php"); ?>
</body>
</html>
