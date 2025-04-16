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
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics - Gestion Commande Jouets</title>
</head>
<?php
    include("../includes/administrator-header.php");
?>

<main>
    <div id="gestion-commande-jouets" class="home-container">
        <div class="inner1-home-container">
            <h2>Gestion Commande Jouets</h2>
            <div class="sep2"></div>
            <?php if (isset($_SESSION["user_id"])){ ?>
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion de Gestion Commande Jouets !</p>
                <!-- SECTION GESTION DES COMMANDES -->
                <div class="liste-c-j">
                    <h3>Liste des Gestion Commande Jouets</h3>
                    <fieldset>
                        <?php
                            // AFFICHAGE DES MESSAGES DE RETOUR
                            if (isset($_SESSION['message'] )){
                                echo $_SESSION['message'];
                                unset($_SESSION['message']); // Nettoyage du message après affichage
                            }
                        ?>
                        <?php
                        // CONNEXION À LA BASE DE DONNÉES
                        $idconn = connexoci("my-param", "oracle2");
                        if (!$idconn) {
                            $e = oci_error();
                            throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                        }

                        // TRAITEMENT DE LA SUPPRESSION D'UNE COMMANDE
                        if (isset($_POST['supprimer_commande'])) {
                            // Récupération des identifiants de la commande à supprimer
                            $id_jouet = $_POST['id_jouet'];
                            $id_enfant = $_POST['id_enfant'];
                            $date_commande = $_POST['date_commande'];
                            
                            // Préparation de la requête de suppression
                            $delete_query = "DELETE FROM commande_jouets 
                                        WHERE id_jouet = :id_jouet 
                                        AND id_enfant = :id_enfant 
                                        AND date_commande = TO_DATE(:date_commande, 'YYYY-MM-DD')";
                            
                            $stid_delete = oci_parse($idconn, $delete_query);
                            // Liaison des paramètres
                            oci_bind_by_name($stid_delete, ':id_jouet', $id_jouet);
                            oci_bind_by_name($stid_delete, ':id_enfant', $id_enfant);
                            oci_bind_by_name($stid_delete, ':date_commande', $date_commande);
                            
                            // Exécution de la suppression
                            if (oci_execute($stid_delete)) {
                                $_SESSION['message']="<p class='success'>Commande supprimée avec succès!</p>";
                                // Redirection pour éviter la resoumission
                                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                exit();
                            } else {
                                $e = oci_error($stid_delete);
                                $_SESSION['message']="<p class='error'>Erreur lors de la suppression: " . htmlspecialchars($e['message'])."</p>";
                                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                exit();
                            }
                            oci_free_statement($stid_delete);
                        }

                        // REQUÊTE POUR RÉCUPÉRER LES COMMANDES
                        $query = "SELECT 
                                    e.id_enfant,
                                    e.nom AS nom_enfant,
                                    e.prenom AS prenom_enfant,
                                    j.id_jouet,
                                    j.nom_jouet AS jouet_commande,
                                    TO_CHAR(cj.date_commande, 'YYYY-MM-DD') AS date_commande_format,
                                    TO_CHAR(cj.date_commande, 'DD/MM/YYYY') AS date_commande_affichage
                                FROM 
                                    les_enfants e, 
                                    commande_jouets cj, 
                                    les_jouets j
                                WHERE 
                                    e.id_enfant = cj.id_enfant
                                    AND cj.id_jouet = j.id_jouet
                                ORDER BY 
                                    cj.date_commande DESC, 
                                    e.nom, 
                                    e.prenom";

                        $stid = oci_parse($idconn, $query);
                        oci_execute($stid);
                        ?>

                        <h1>Liste des commandes passées</h1>
                                        
                        <!-- FORMULAIRE DE FILTRAGE PAR DATE -->
                        <form method="post" action="">
                            <label for="date_debut">Date début :</label>
                            <input type="date" id="date_debut" name="date_debut">
                            
                            <label for="date_fin">Date fin :</label>
                            <input type="date" id="date_fin" name="date_fin">
                            
                            <input type="submit" name="filtrer" value="Filtrer">
                            <input type="submit" name="tout_afficher" value="Tout afficher">
                        </form>
                        
                        <?php
                        // TRAITEMENT DU FILTRAGE PAR DATE
                        if (isset($_POST['filtrer'])) {
                            $date_debut = $_POST['date_debut'];
                            $date_fin = $_POST['date_fin'];
                            
                            // Requête avec filtre de date
                            $query_filtre = "SELECT 
                                                e.id_enfant,
                                                e.nom AS nom_enfant,
                                                e.prenom AS prenom_enfant,
                                                j.id_jouet,
                                                j.nom_jouet AS jouet_commande,
                                                TO_CHAR(cj.date_commande, 'YYYY-MM-DD') AS date_commande_format,
                                                TO_CHAR(cj.date_commande, 'DD/MM/YYYY') AS date_commande_affichage
                                            FROM les_enfants e, 
                                                commande_jouets cj, 
                                                les_jouets j
                                            WHERE e.id_enfant = cj.id_enfant
                                            AND cj.id_jouet = j.id_jouet
                                            AND cj.date_commande BETWEEN TO_DATE(:date_debut, 'YYYY-MM-DD') 
                                            AND TO_DATE(:date_fin, 'YYYY-MM-DD')
                                            ORDER BY cj.date_commande DESC, e.nom, e.prenom";
                            
                            $stid = oci_parse($idconn, $query_filtre);
                            oci_bind_by_name($stid, ':date_debut', $date_debut);
                            oci_bind_by_name($stid, ':date_fin', $date_fin);
                            oci_execute($stid);
                        }
                        ?>

                        <!-- TABLEAU DES COMMANDES -->
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>ID enfant</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Jouet commandé</th>
                                    <th>Date de commande</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // AFFICHAGE DE CHAQUE COMMANDE
                                while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['ID_ENFANT']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['NOM_ENFANT']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['PRENOM_ENFANT']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['JOUET_COMMANDE']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['DATE_COMMANDE_AFFICHAGE']) . "</td>";
                                    echo "<td>
                                        <!-- Formulaire de suppression avec confirmation JS -->
                                        <form method='post' onsubmit='return confirm(\"Êtes-vous sûr de vouloir supprimer cette commande ?\");'>
                                            <input type='hidden' name='id_jouet' value='" . htmlspecialchars($row['ID_JOUET']) . "'>
                                            <input type='hidden' name='id_enfant' value='" . htmlspecialchars($row['ID_ENFANT']) . "'>
                                            <input type='hidden' name='date_commande' value='" . htmlspecialchars($row['DATE_COMMANDE_FORMAT']) . "'>
                                            <button type='submit' name='supprimer_commande'>Supprimer</button>
                                        </form>
                                    </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <?php
                        // Libération des ressources
                        oci_free_statement($stid);
                        oci_close($idconn);
                        ?>

                    </fieldset>
                </div>

                <fieldset>
                <?php
                try {
                    // Connexion à la base de données Oracle avec oci_connect
                    $idconn = connexoci("my-param", "oracle2");

                    // Requête SQL
                    $requete = "SELECT * FROM Les_jouets";

                    // Préparation de la requête
                    $stmt = oci_parse($idconn, $requete);

                    // Exécution de la requête
                    oci_execute($stmt);

                    // Récupération des résultats
                    $results = [];
                    while ($row = oci_fetch_assoc($stmt)) {
                    $results[] = $row;
                    }

                    // Libération des ressources
                    oci_free_statement($stmt); // Libère la ressource du statement
                    oci_close($idconn);

                } catch (Exception $e) {
                    echo "Erreur : " . $e->getMessage();
                    exit;
                }
                ?>
      
                <h2>Envoyer une commande pour un enfant:</h2>
                <p>Créez une liste de souhaits personnalisées.</p>

                <form method="post" action="">
                    <!-- Champ pour l'identifiant de l'enfant -->
                    <label for="id_enfant">ID Enfant:</label>
                    <input type="text" id="id_enfant" name="id_enfant" maxlength="8" placeholder="ID Enfant" required>

                    <?php if (!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <div class="gift-item">
                                    <!-- Case à cocher pour sélectionner le cadeau -->
                                    <input type="checkbox" name="cadeaux_selectionnes[]" value="<?php echo htmlspecialchars($row['ID_JOUET']); ?>">
                                    <!-- Image et nom du jouet -->
                                    <img src="../images/img/<?php echo htmlspecialchars($row['NOM_JOUET']); ?>.jpg" alt="<?php echo htmlspecialchars($row['NOM_JOUET']); ?>">
                                    <p class="gift-name"><?php echo htmlspecialchars($row['NOM_JOUET']); ?></p>
                                </div>
                            <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun cadeau disponible pour le moment.</p>
                    <?php endif; ?>

                    <button type="submit" name="envoyer">Envoyer</button>
                </form>
            </fieldset>
			<?php
               
                // Traitement du formulaire soumis
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['envoyer'])) {

                    // Récupérer les données du formulaire
                    $id_enfant = $_POST['id_enfant'];
                    $cadeaux_selectionnes = $_POST['cadeaux_selectionnes'] ?? []; // Tableau des cadeaux sélectionnés

                    // Valider et sécuriser les données
                    $id_enfant = htmlspecialchars($id_enfant);

                    try {

                        // Connexion à la base de données Oracle avec oci_connect
        			    $idconn = connexoci("my-param", "oracle2");

						$id_enfant_like = $id_enfant .'%';
						// Vérifier si l'ID enfant existe déjà
						$requete = "SELECT COUNT(*) AS COUNT FROM LES_ENFANTS WHERE ID_ENFANT LIKE :id_enfant";
						$stmt = oci_parse($idconn, $requete);
						oci_bind_by_name($stmt,":id_enfant",$id_enfant_like);
						oci_execute($stmt);
						$result_check=oci_fetch_assoc($stmt);
						oci_free_statement($stmt);// Libérer la ressource
						// Vérification du résultat
						if ($result_check['COUNT'] == 0) {
                            $_SESSION['message']="<div class='error'>L'enfant n'existe pas dans la liste des enfants !.</div>";
                            // Redirection vers la même page après déconnexion
                            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                            exit();
						}

						if (empty($cadeaux_selectionnes)) {
							echo "<div class='error'>Veuillez sélectionner au moins un jouet.</div>";
							exit();
						}else{
                            $cmd=false;
                            // Insérer les cadeaux sélectionnés dans la table COMMANDE_JOUETS				
                            foreach ($cadeaux_selectionnes as $id_jouet) {
                                $id_jouet_like = $id_jouet .'%';
                                $requete_verif = "SELECT count(*) AS COUNTJ FROM COMMANDE_JOUETS  WHERE id_enfant LIKE :id_enfant AND id_jouet LIKE :id_jouet";
                                $stmt_verif = oci_parse($idconn, $requete_verif);

                                oci_bind_by_name($stmt_verif, ":id_enfant", $id_enfant_like);
                                oci_bind_by_name($stmt_verif, ":id_jouet", $id_jouet_like);
                                oci_execute($stmt_verif);
                                $result_check=oci_fetch_assoc($stmt_verif);

                                if (!$result_check) {
                                    $e = oci_error($stmt_verif);
                                    throw new Exception("Erreur lors de l'insertion du jouet : " . $e['message']);
                                }

                                // Libération des ressources
                                oci_free_statement($stmt_verif);

                                if ( $result_check['COUNTJ'] == 0 ){

                                    $requete_insertion2 = "INSERT INTO COMMANDE_JOUETS (Id_enfant, Id_jouet, DATE_COMMANDE) VALUES (:id_enfant, :id_jouet, TO_DATE(:date_commande, 'DD/MM/YYYY'))";
                                    $stmt_insertion2 = oci_parse($idconn, $requete_insertion2);
                                    $date_commande = date('d/m/Y'); // Format correct pour la date

                                    oci_bind_by_name($stmt_insertion2, ":id_enfant", $id_enfant);
                                    oci_bind_by_name($stmt_insertion2, ":id_jouet", $id_jouet);
                                    oci_bind_by_name($stmt_insertion2, ":date_commande", $date_commande);

                                    if (!oci_execute($stmt_insertion2)) {
                                        $e = oci_error($stmt_insertion2);
                                        throw new Exception("Erreur lors de l'insertion du jouet : " . $e['message']);
                                    }else{
                                        $cmd=true;
                                    }
                                    // Libération des ressources
                                    oci_free_statement($stmt_insertion2);
                                }
                            }

                            // Valider la transaction
                            $validation = "commit";
                            $stmtv = oci_parse($idconn, $validation);
                            oci_execute($stmtv);
                            // Libération des ressources
                            oci_free_statement($stmtv);
                            if ($cmd){
                                $_SESSION['message']="<div class='success'>Commande envoyée avec succès !</div>";
                                // Redirection vers la même page après déconnexion
                                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                                exit();
                            }
						}
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

            <?php }else{ ?>
                <p>Veuillez vous connecter pour accéder à la gestion de Gestion Commande Jouets.</p>
                <p><a href="compte.php">Se connecter</a></p>
            <?php } ?>
        </div>
    </div>
</main>

<?php
    include("../includes/administrator-footer.php");
?>
