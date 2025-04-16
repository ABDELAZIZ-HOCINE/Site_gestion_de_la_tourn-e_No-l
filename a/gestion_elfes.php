<?php
session_start();
include_once("../connexion-base/connex.inc.php");

// Fonction pour gérer les valeurs NULL
function cleanPostValue($value) {
    return (!empty($value) || $value === '0') ? $value : null;
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
    <title>SantaLogistics - Gestion Elfes</title>
</head>
<body>
    <?php include("../includes/administrator-header.php"); ?>

<main>
    <div id="gestion-elfes" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion des Elfes</h2>
            <div class="sep2"></div>
            <?php if (isset($_SESSION["user_id"])): ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion des Elfes !</p>

                <!-- Section Liste des Elfes -->
                <div class="liste-elfes">
                    <h3>Liste des Elfes</h3>
                    <fieldset>
                        <?php
                            // Connexion à la base de données
                            $idconn = connexoci("my-param", "oracle2");
                            if (!$idconn) {
                                $e = oci_error();
                                throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                            }
                            
                            // TRAITEMENT DES ACTIONS
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                // MODIFICATION D'UN ELFE
                                if (isset($_POST['modifier'])) {
                                    $id_elfe = $_POST['id_elfe'];
                                    $nom = $_POST['nom'];
                                    $id_traineau = cleanPostValue($_POST['id_traineau']);
                                    $id_equipe_fabrication = cleanPostValue($_POST['id_equipe_fabrication']);
                                    $id_equipe_logistique = cleanPostValue($_POST['id_equipe_logistique']);
                                    
                                    $sql_update = "UPDATE les_elfes SET 
                                                nom = :nom, 
                                                id_traineau = :id_traineau, 
                                                id_equipe_fabrication = :id_equipe_fabrication, 
                                                id_equipe_logistique = :id_equipe_logistique 
                                                WHERE id_elfe = :id_elfe";
                                    
                                    $stid_update = oci_parse($idconn, $sql_update);
                                    oci_bind_by_name($stid_update, ':nom', $nom);
                                    oci_bind_by_name($stid_update, ':id_traineau', $id_traineau);
                                    oci_bind_by_name($stid_update, ':id_equipe_fabrication', $id_equipe_fabrication);
                                    oci_bind_by_name($stid_update, ':id_equipe_logistique', $id_equipe_logistique);
                                    oci_bind_by_name($stid_update, ':id_elfe', $id_elfe);
                                    
                                    if (oci_execute($stid_update)) {
                                        $_SESSION['message'] = "<p class='success'>Elfe ID $id_elfe modifié avec succès !</p>";
                                    } else {
                                        $e = oci_error($stid_update);
                                        $_SESSION['message'] = "<p class='error'>Erreur modification: " . htmlspecialchars($e['message']) . "</p>";
                                    }
                                    oci_free_statement($stid_update);
                                    header("Location: ".$_SERVER['PHP_SELF']);
                                    exit();
                                }
                                // SUPPRESSION D'UN ELFE
                                elseif (isset($_POST['supprimer'])) {
                                    $id_elfe = $_POST['id_elfe'];
                                    
                                    $sql_delete = "DELETE FROM les_elfes WHERE id_elfe = :id_elfe";
                                    $stid_delete = oci_parse($idconn, $sql_delete);
                                    oci_bind_by_name($stid_delete, ':id_elfe', $id_elfe);
                                    
                                    if (oci_execute($stid_delete)) {
                                        $_SESSION['message'] = "<p class='success'>Elfe ID $id_elfe supprimé avec succès !</p>";
                                    } else {
                                        $e = oci_error($stid_delete);
                                        $_SESSION['message'] = "<p class='error'>Erreur suppression: " . htmlspecialchars($e['message']) . "</p>";
                                    }
                                    oci_free_statement($stid_delete);
                                    header("Location: ".$_SERVER['PHP_SELF']);
                                    exit();
                                }
                                // AJOUT D'UN NOUVEL ELFE
                                // Dans la partie traitement de l'ajout (remplacer la section existante)
elseif (isset($_POST['ajouter'])) {
    $id_elfe = $_POST['new_id_elfe'];
    $nom = $_POST['new_nom'];
    $id_traineau = cleanPostValue($_POST['new_id_traineau']);
    $id_equipe_fabrication = cleanPostValue($_POST['new_id_equipe_fabrication']);
    $id_equipe_logistique = cleanPostValue($_POST['new_id_equipe_logistique']);
    
    // Validation du format de l'ID
    if (!preg_match('/^ELF\d+$/i', $id_elfe)) {
        $_SESSION['message'] = "<p class='error'>Format d'ID incorrect. Utilisez le format ELF suivi de chiffres (ex: ELF10)</p>";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    $sql_insert = "INSERT INTO les_elfes (id_elfe, nom, id_traineau, id_equipe_fabrication, id_equipe_logistique) 
                  VALUES (:id_elfe, :nom, :id_traineau, :id_equipe_fabrication, :id_equipe_logistique)";
    
    $stid_insert = oci_parse($idconn, $sql_insert);
    oci_bind_by_name($stid_insert, ':id_elfe', $id_elfe);
    oci_bind_by_name($stid_insert, ':nom', $nom);
    oci_bind_by_name($stid_insert, ':id_traineau', $id_traineau);
    oci_bind_by_name($stid_insert, ':id_equipe_fabrication', $id_equipe_fabrication);
    oci_bind_by_name($stid_insert, ':id_equipe_logistique', $id_equipe_logistique);
    
    if (oci_execute($stid_insert)) {
        $_SESSION['message'] = "<p class='success'>Nouvel elfe $id_elfe ajouté avec succès !</p>";
    } else {
        $e = oci_error($stid_insert);
        $_SESSION['message'] = "<p class='error'>Erreur ajout: " . htmlspecialchars($e['message']) . "</p>";
    }
    oci_free_statement($stid_insert);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}		
		                    }
                            
                            // Récupération des données pour les listes déroulantes
                            $traineaux = [];
                            $sql_traineaux = "SELECT id_traineau, num_traineau FROM les_traineaux ORDER BY num_traineau";
                            $stid_t = oci_parse($idconn, $sql_traineaux);
                            oci_execute($stid_t);
                            while ($row = oci_fetch_array($stid_t, OCI_ASSOC)) {
                                $traineaux[$row['ID_TRAINEAU']] = $row['NUM_TRAINEAU'];
                            }
                            oci_free_statement($stid_t);
                            
                            $equipes_fab = [];
                            $sql_equipes_fab = "SELECT id_equipe_fabrication FROM les_equipes_fabrication ORDER BY id_equipe_fabrication";
                            $stid_ef = oci_parse($idconn, $sql_equipes_fab);
                            oci_execute($stid_ef);
                            while ($row = oci_fetch_array($stid_ef, OCI_ASSOC)) {
                                $equipes_fab[] = $row['ID_EQUIPE_FABRICATION'];
                            }
                            oci_free_statement($stid_ef);
                            
                            $equipes_log = [];
                            $sql_equipes_log = "SELECT id_equipe_logistique FROM les_equipes_logistiques ORDER BY id_equipe_logistique";
                            $stid_el = oci_parse($idconn, $sql_equipes_log);
                            oci_execute($stid_el);
                            while ($row = oci_fetch_array($stid_el, OCI_ASSOC)) {
                                $equipes_log[] = $row['ID_EQUIPE_LOGISTIQUE'];
                            }
                            oci_free_statement($stid_el);
                            
                            // Récupération de la liste des elfes
                            $sql_select = "SELECT e.id_elfe, e.nom, 
                                        t.id_traineau, t.num_traineau,
                                        ef.id_equipe_fabrication,
                                        el.id_equipe_logistique
                                        FROM les_elfes e
                                        LEFT JOIN les_traineaux t ON e.id_traineau = t.id_traineau
                                        LEFT JOIN les_equipes_fabrication ef ON e.id_equipe_fabrication = ef.id_equipe_fabrication
                                        LEFT JOIN les_equipes_logistiques el ON e.id_equipe_logistique = el.id_equipe_logistique
                                        ORDER BY e.id_elfe";
                            $stid_select = oci_parse($idconn, $sql_select);
                            oci_execute($stid_select);
                        ?>

                        <!-- Affichage des messages -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <?= $_SESSION['message'] ?>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>

		 <!-- Formulaire d'ajout d'un nouvel elfe -->
	<!-- Formulaire d'ajout d'un nouvel elfe -->
	<div class="ajout-elfe">
	    <h4>Ajouter un nouvel elfe</h4>
	    <form method="post" class="form-style">
		<input type="text" name="new_id_elfe" placeholder="ID Elfe" 
		       pattern="ELF[0-9]+" title="Format: ELF suivi de chiffres (ex: ELF10)" required>
		<input type="text" name="new_nom" placeholder="Nom Elfe" required>
		<select name="new_id_traineau">
		    <option value="">- Sans traîneau -</option>
		    <?php foreach ($traineaux as $id => $num): ?>
		        <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($num) ?></option>
		    <?php endforeach; ?>
		</select>
		<select name="new_id_equipe_fabrication">
		    <option value="">- Sans équipe fabrication -</option>
		    <?php foreach ($equipes_fab as $id): ?>
		        <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($id) ?></option>
		    <?php endforeach; ?>
		</select>
		<select name="new_id_equipe_logistique">
		    <option value="">- Sans équipe logistique -</option>
		    <?php foreach ($equipes_log as $id): ?>
		        <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($id) ?></option>
		    <?php endforeach; ?>
		</select>
		<button type="submit" name="ajouter">Ajouter</button>
	    </form>
	</div>
		                <!-- Tableau des elfes -->
                        <table class="table-style">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>ID Traineau</th>
                                    <th>ID Équipe Fabrication</th>
                                    <th>ID Équipe Logistique</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = oci_fetch_array($stid_select, OCI_ASSOC)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ID_ELFE']) ?></td>
                                        <td>
                                            <form method="post" class="form-inline">
                                                <input type="hidden" name="id_elfe" value="<?= htmlspecialchars($row['ID_ELFE']) ?>">
                                                <input type="text" name="nom" value="<?= htmlspecialchars($row['NOM']) ?>" required>
                                        </td>
                                        <td>
                                            <select name="id_traineau">
                                                <option value="">-- Non affecté --</option>
                                                <?php foreach ($traineaux as $id => $num): ?>
                                                    <option value="<?= htmlspecialchars($id) ?>" <?= ($id == $row['ID_TRAINEAU']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($num) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="id_equipe_fabrication">
                                                <option value="">-- Non affecté --</option>
                                                <?php foreach ($equipes_fab as $id): ?>
                                                    <option value="<?= htmlspecialchars($id) ?>" <?= ($id == $row['ID_EQUIPE_FABRICATION']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($id) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="id_equipe_logistique">
                                                <option value="">-- Non affecté --</option>
                                                <?php foreach ($equipes_log as $id): ?>
                                                    <option value="<?= htmlspecialchars($id) ?>" <?= ($id == $row['ID_EQUIPE_LOGISTIQUE']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($id) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="submit" name="modifier">Modifier</button>
                                            <button type="submit" name="supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet elfe ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <?php
                            // Libération des ressources
                            oci_free_statement($stid_select);
                            oci_close($idconn);
                        ?>
                    </fieldset>
                </div>

            <?php else: ?>
                <p>Veuillez vous connecter pour accéder à la gestion des Elfes.</p>
                <p><a href="compte.php">Se connecter</a></p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include("../includes/administrator-footer.php"); ?>
</body>
</html>
