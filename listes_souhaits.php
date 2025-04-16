<?php
	session_start();
    // Inclusion des paramètres de connexion
    include_once("connexion-base/connex.inc.php");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="css/style-of-my-web-site-cust-index.css">
    <link rel="icon" type="image/x-icon" href="images/icons/icon.png">
    <title>SantaLogistics</title>
</head>
<?php
	// Inclusion de l'en-tête
	include("includes/cust-header.php");
	include('includes/functions.php');
?>


<main>
    <!-- Listes de souhaits ----------------------------------------------------------------------------------------------------->
    <div id="listes_souhaits" class="home-container">
        <div class="inner1-home-container">
            <div class="sep2"></div>
            <h2>Listes de souhaits</h2>
		    <p>Bienvenue, !</p>
		    <p>Créez votre liste de souhaits personnalisées.</p>

		    <form method="post" action="">
				<!-- Champ pour l'identifiant de l'enfant -->
				<label for="id_enfant">ID Enfant:</label>
				<input type="text" id="id_enfant" name="id_enfant" maxlength="8" placeholder="ID Enfant" required>

				<!-- Champ pour le nom de l'enfant -->
				<label for="nom_enfant">Nom de l'enfant:</label>
				<input type="text" id="nom_enfant" name="nom_enfant" maxlength="50" placeholder="Nom de l'enfant" required>

				<!-- Champ pour le prénom de l'enfant -->
				<label for="prenom_enfant">Prénom de l'enfant:</label>
				<input type="text" id="prenom_enfant" name="prenom_enfant" maxlength="50" placeholder="Prénom de l'enfant" required>

				<!-- Champ pour l'adresse de l'enfant -->
				<label for="adresse">Adresse:</label>
				<input type="text" id="adresse" name="adresse" maxlength="50" placeholder="Adresse" required>


				<?php if (!empty($results)): ?>
						<?php foreach ($results as $row): ?>
							<div class="gift-item">
								<!-- Case à cocher pour sélectionner le cadeau -->
								<input type="checkbox" name="cadeaux_selectionnes[]" value="<?php echo htmlspecialchars($row['ID_JOUET']); ?>">
								<!-- Image et nom du jouet -->
								<img src="images/img/<?php echo htmlspecialchars($row['NOM_JOUET']); ?>.jpg" alt="<?php echo htmlspecialchars($row['NOM_JOUET']); ?>">
								<p class="gift-name"><?php echo htmlspecialchars($row['NOM_JOUET']); ?></p>
							</div>
						<?php endforeach; ?>
				<?php else: ?>
					<p>Aucun cadeau disponible pour le moment.</p>
				<?php endif; ?>

				<button type="submit" name="envoyer">Envoyer</button>
		    </form>
		<?php
		        if (isset($_SESSION['message'] )){
		            echo $_SESSION['message'];
		            unset($_SESSION['message']);
		        }
                ?>

			<?php
                // Traitement du formulaire soumis
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['envoyer'])) {
                    // Récupérer les données du formulaire
                    $id_enfant = $_POST['id_enfant'];
                    $nom_enfant = $_POST['nom_enfant'];
                    $prenom_enfant = $_POST['prenom_enfant'];
                    $adresse = $_POST['adresse'];
                    $cadeaux_selectionnes = $_POST['cadeaux_selectionnes'] ?? []; // Tableau des cadeaux sélectionnés

                    // Valider et sécuriser les données
                    $id_enfant = htmlspecialchars($id_enfant);
                    $nom_enfant = htmlspecialchars($nom_enfant);
                    $prenom_enfant = htmlspecialchars($prenom_enfant);
                    $adresse = htmlspecialchars($adresse);

                    try {
                        // Connexion à la base de données Oracle avec oci_connect
                        $idconn = connexoci("my-param", "oracle2");
                        if (!$idconn) {
                            $e = oci_error();
                            throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                        }

						$id_enfant_like = $id_enfant .'%';
						// Vérifier si l'ID enfant existe déjà
						$requete = "SELECT COUNT(*) AS COUNT FROM LES_ENFANTS WHERE ID_ENFANT LIKE :id_enfant";
						$stmt = oci_parse($idconn, $requete);
						oci_bind_by_name($stmt,":id_enfant",$id_enfant_like);
						oci_execute($stmt);
						$result_check=oci_fetch_assoc($stmt);
						oci_free_statement($stmt); // Libérer la ressource
						// Vérification du résultat
						//echo $result_check['COUNT'];
						if ($result_check['COUNT'] == 0) {
							// Insérer les informations de l'enfant dans la table LES_ENFANTS
							$requete_insertion = "INSERT INTO LES_ENFANTS (ID_ENFANT, NOM, PRENOM, ADRESSE) VALUES (:id_enfant, :nom_enfant, :prenom_enfant, :adresse)";
							$stmt_insertion = oci_parse($idconn, $requete_insertion);

							oci_bind_by_name($stmt_insertion, ":id_enfant", $id_enfant);
							oci_bind_by_name($stmt_insertion, ":nom_enfant", $nom_enfant);
							oci_bind_by_name($stmt_insertion, ":prenom_enfant", $prenom_enfant);
							oci_bind_by_name($stmt_insertion, ":adresse", $adresse);

							if (!oci_execute($stmt_insertion)) {
								$e = oci_error($stmt_insertion);
								throw new Exception("Une erreur s'est produite lors de l'insertion des information de l'enfant.!!!" . $e['message']);
							}
							// Libération des ressources
							oci_free_statement($stmt_insertion);
						}

						if (empty($cadeaux_selectionnes)) {
							echo "<div class='error'>Veuillez sélectionner au moins un jouet.</div>";
							exit();
						}else{
							$id_enfant_like = $id_enfant .'%';
							// Vérifier si l'enfant a déjà passé une commande
							$requete = "SELECT COUNT(*) AS COUNT FROM COMMANDE_JOUETS WHERE ID_ENFANT LIKE :id_enfant";
							$stmt = oci_parse($idconn, $requete);
							oci_bind_by_name($stmt,":id_enfant",$id_enfant_like);
							oci_execute($stmt);
							$result_check=oci_fetch_assoc($stmt);
							oci_free_statement($stmt); // Libérer la ressource
							// Vérification du résultat
							if ($result_check['COUNT'] == 0) {
								// Insérer les cadeaux sélectionnés dans la table COMMANDE_JOUETS				
								foreach ($cadeaux_selectionnes as $id_jouet) {
									$requete_insertion2 = "INSERT INTO COMMANDE_JOUETS (Id_enfant, Id_jouet, DATE_COMMANDE) VALUES (:id_enfant, :id_jouet, TO_DATE(:date_commande, 'DD/MM/YYYY'))";
									$stmt_insertion2 = oci_parse($idconn, $requete_insertion2);
									$date_commande = date('d/m/Y'); // Format correct pour la date

									oci_bind_by_name($stmt_insertion2, ":id_enfant", $id_enfant);
									oci_bind_by_name($stmt_insertion2, ":id_jouet", $id_jouet);
									oci_bind_by_name($stmt_insertion2, ":date_commande", $date_commande);

									if (!oci_execute($stmt_insertion2)) {
										$e = oci_error($stmt_insertion2);
										throw new Exception("Erreur lors de l'insertion du jouet : " . $e['message']);
									}
									// Libération des ressources
									oci_free_statement($stmt_insertion2);
								}

								// Valider la transaction
								$validation = "commit";
								$stmtv = oci_parse($idconn, $validation);
								oci_execute($stmtv);
								// Libération des ressources
								oci_free_statement($stmtv);
							
								$_SESSION['message']="<div class='success'>Les informations de l'enfant et les jouets sélectionnés ont été envoyés avec succès !</div>";
							    	// Redirection vers la même page après déconnexion
							        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
							        exit();
							} else {
								$_SESSION['message']="<div class='error'>L'enfant a déjà passé une commande !, Changez l'ID ENFANT pour passer une autre commande </div>";
							    	// Redirection vers la même page après déconnexion
							        echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
							        exit();
							}
						}
                        oci_close($idconn);

                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage();
                        // Annuler la transaction en cas d'erreur
                        if (isset($idconn)) {
                            $rollback = "rollback";
                            $stmtr = oci_parse($idconn, $rollback);
                            oci_execute($stmtr);
                            oci_close($idconn);
                        }
                    }
                }
            ?>
        </div>
    </div>
</main>

<?php
// Inclusion du pied de page
include("includes/cust-footer.php");
?>
