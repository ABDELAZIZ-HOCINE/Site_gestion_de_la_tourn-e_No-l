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
    <title>SantaLogistics - Historique Fabrication</title>
</head>
<?php
    // Inclusion de l'en-tête administrateur
    include("../includes/administrator-header.php");
?>

<main>
    <div id="historique-fabrication" class="home-container">
        <div class="inner1-home-container">
            <h2>Historique de Fabrication</h2>
            <div class="sep2"></div>
            <?php if (isset($_SESSION["user_id"])){ ?>
                <!-- Message de bienvenue avec le nom de l'utilisateur connecté -->
                <p>Bienvenue <b><?php echo htmlspecialchars($_SESSION["user_name"]); ?></b> dans la gestion de l'historique de fabrication !</p>

                <!-- Section Recherche -->
                <div class="recherche-fabrication">
                    <h3>Recherche dans l'historique</h3>
                    <fieldset>
                        <form method="get" action="">
                            <label for="type_recherche">Type de recherche :</label>
                            <select name="type_recherche" id="type_recherche">
                                <option value="jouet">Par jouet</option>
                                <option value="enfant">Par enfant</option>
                                <option value="tous">Tous les historiques</option>
                            </select>
                            
                            <div id="champ-jouet" class="champ-recherche">
                                <label for="id_jouet">ID Jouet :</label>
                                <input type="text" id="id_jouet" name="id_jouet" placeholder="Ex: JOU001">
                            </div>
                            
                            <div id="champ-enfant" class="champ-recherche" style="display:none;">
                                <label for="id_enfant">ID Enfant :</label>
                                <input type="text" id="id_enfant" name="id_enfant" placeholder="Ex: ENF001">
                            </div>
                            
                            <button type="submit" name="rechercher">Rechercher</button>
                        </form>
                    </fieldset>
                </div>

                <!-- Section Résultats -->
                <div class="resultats-fabrication">
                    <?php
                        // Affichage des messages de session
                        if (isset($_SESSION['message'])) {
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                        }
                        
                        // CONNEXION À LA BASE DE DONNÉES ORACLE
                        $idconn = connexoci("my-param", "oracle2");
                        if (!$idconn) {
                            $e = oci_error();
                            throw new Exception("Erreur de connexion à la base de données : " . $e['message']);
                        }
                        
                        // TRAITEMENT DE LA RECHERCHE
                        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['rechercher'])) {
                            $type_recherche = $_GET['type_recherche'];
                            
                            if ($type_recherche === 'jouet' && !empty($_GET['id_jouet'])) {
                                // Requête 19: Historique pour un jouet spécifique
                                $id_jouet = $_GET['id_jouet'];
                                $id_jouet_like = $_GET['id_jouet'].'%';
                                $sql = "SELECT DISTINCT
                                    J.ID_JOUET,
                                    J.NOM_JOUET,
                                    CASE 
                                        WHEN PP.ID_ATELIER IS NOT NULL THEN 'Atelier: ' || PP.ID_ATELIER
                                        WHEN CA.ID_SOUS_TRAITANT IS NOT NULL THEN 'Sous-traitant: ' || ST.NOM_SOUS_TRAITANT
                                    END AS lieu_fabrication,
                                    HF.DESCRIPTION_ETAPE,
                                    HF.STATUT_FABRICATION,
                                    TO_CHAR(HF.DATE_ENTREE, 'DD/MM/YYYY') AS DATE_ENTREE,
                                    TO_CHAR(HF.DATE_SORTIE, 'DD/MM/YYYY') AS DATE_SORTIE 
                                FROM 
                                    LES_JOUETS J,
                                    LES_ATELIERS A,
                                    PASSE_PAR PP,
                                    CONFIE_AU CA,
                                    HISTORIQUE_FABRICATION HF,
                                    LES_SOUS_TRAITANTS ST,
                                    LES_EQUIPES_FABRICATION EF
                                WHERE 
                                    J.ID_JOUET = PP.ID_JOUET(+)
                                    AND J.ID_JOUET = CA.ID_JOUET(+)
                                    AND PP.ID_ATELIER = A.ID_ATELIER
                                    AND PP.ID_HISTORIQUE = HF.ID_HISTORIQUE
                                    AND CA.ID_HISTORIQUE(+) = HF.ID_HISTORIQUE
                                    AND CA.ID_SOUS_TRAITANT(+) = ST.ID_SOUS_TRAITANT
                                    AND EF.ID_ATELIER(+) = A.ID_ATELIER
                                    AND J.ID_JOUET LIKE :id_jouet ORDER BY DATE_SORTIE asc
                                ";
                                
                                $stid = oci_parse($idconn, $sql);
                                oci_bind_by_name($stid, ':id_jouet', $id_jouet_like);
                                $titre = "Historique de fabrication pour le jouet $id_jouet";
                                
                            } elseif ($type_recherche === 'enfant' && !empty($_GET['id_enfant'])) {
                                // Requête 21: Historique pour les jouets commandés par un enfant
                                $id_enfant = $_GET['id_enfant'];
                                $id_enfant_like = $_GET['id_enfant']."%";

                                $sql = "SELECT DISTINCT
                                    ENF.NOM,
                                    ENF.PRENOM,
                                    J.ID_JOUET,
                                    J.NOM_JOUET,
                                    CASE
                                        WHEN PP.ID_ATELIER IS NOT NULL THEN 'Atelier: ' || PP.ID_ATELIER
                                        WHEN CA.ID_SOUS_TRAITANT IS NOT NULL THEN 'Sous-traitant: ' || ST.NOM_SOUS_TRAITANT
                                    END AS lieu_fabrication,
                                    HF.DESCRIPTION_ETAPE,
                                    HF.STATUT_FABRICATION,
                                    TO_CHAR(HF.DATE_ENTREE, 'DD/MM/YYYY') AS DATE_ENTREE,
                                    TO_CHAR(HF.DATE_SORTIE, 'DD/MM/YYYY') AS DATE_SORTIE 
                                FROM 
                                    LES_JOUETS J,
                                    PASSE_PAR PP,
                                    CONFIE_AU CA,
                                    LES_ATELIERS A,
                                    LES_SOUS_TRAITANTS ST,
                                    HISTORIQUE_FABRICATION HF,
                                    LES_EQUIPES_FABRICATION EF,
                                    COMMANDE_JOUETS CJ,
                                    LES_ENFANTS ENF
                                WHERE 
                                    ENF.ID_ENFANT = CJ.ID_ENFANT
                                    AND J.ID_JOUET = CJ.ID_JOUET
                                    AND J.ID_JOUET = PP.ID_JOUET(+)
                                    AND J.ID_JOUET = CA.ID_JOUET(+)
                                    AND PP.ID_ATELIER = A.ID_ATELIER(+)
                                    AND CA.ID_SOUS_TRAITANT = ST.ID_SOUS_TRAITANT(+)
                                    AND HF.ID_HISTORIQUE = PP.ID_HISTORIQUE(+)
                                    AND HF.ID_HISTORIQUE = CA.ID_HISTORIQUE(+)
                                    AND EF.ID_ATELIER = A.ID_ATELIER(+)
                                    AND( PP.ID_ATELIER IS NOT NULL OR CA.ID_SOUS_TRAITANT IS NOT NULL)
                                    AND ENF.ID_ENFANT LIKE :id_enfant
                                ORDER BY 
                                    J.ID_JOUET, DATE_SORTIE ASC";
                                
                                $stid = oci_parse($idconn, $sql);
                                oci_bind_by_name($stid, ':id_enfant', $id_enfant_like);
                                $titre = "Historique de fabrication pour les jouets commandés par l'enfant $id_enfant";
                                
                            } else {
                                // Requête 20: Tous les historiques de fabrication
                                $sql = "SELECT DISTINCT
                                    J.ID_JOUET,
                                    J.NOM_JOUET,
                                    CASE
                                        WHEN PP.ID_ATELIER IS NOT NULL THEN 'Atelier: ' || PP.ID_ATELIER
                                        WHEN CA.ID_SOUS_TRAITANT IS NOT NULL THEN 'Sous-traitant: ' || ST.NOM_SOUS_TRAITANT
                                    END AS lieu_fabrication,
                                    HF.DESCRIPTION_ETAPE,
                                    HF.STATUT_FABRICATION,
                                    TO_CHAR(HF.DATE_ENTREE, 'DD/MM/YYYY') AS DATE_ENTREE,
                                    TO_CHAR(HF.DATE_SORTIE, 'DD/MM/YYYY') AS DATE_SORTIE 
                                FROM 
                                    LES_JOUETS J,
                                    PASSE_PAR PP,
                                    CONFIE_AU CA,
                                    LES_ATELIERS A,
                                    LES_SOUS_TRAITANTS ST,
                                    HISTORIQUE_FABRICATION HF,
                                    LES_EQUIPES_FABRICATION EF
                                WHERE 
                                    J.ID_JOUET = PP.ID_JOUET(+)
                                    AND J.ID_JOUET = CA.ID_JOUET(+)
                                    AND PP.ID_ATELIER = A.ID_ATELIER(+)
                                    AND CA.ID_SOUS_TRAITANT = ST.ID_SOUS_TRAITANT(+)
                                    AND HF.ID_HISTORIQUE = PP.ID_HISTORIQUE(+)
                                    AND HF.ID_HISTORIQUE = CA.ID_HISTORIQUE(+)
                                    AND EF.ID_ATELIER = A.ID_ATELIER(+)
                                    AND( PP.ID_ATELIER IS NOT NULL OR CA.ID_SOUS_TRAITANT IS NOT NULL)
                                ORDER BY 
                                    J.ID_JOUET, DATE_SORTIE ASC";
                                
                                $stid = oci_parse($idconn, $sql);
                                $titre = "Tous les historiques de fabrication";
                            }
                            
                            // Exécution de la requête
                            if (oci_execute($stid)) {
                                echo "<h3>$titre</h3>";
                                echo "<table class='table-style'>";
                                echo "<thead>";
                                echo "<tr>";
                                
                                // Affichage des colonnes en fonction du type de requête
                                if ($type_recherche === 'enfant') {
                                    echo "<th>Nom</th>";
                                    echo "<th>Prénom</th>";
                                }
                                
                                echo "<th>ID Jouet</th>";
                                echo "<th>Nom Jouet</th>";
                                echo "<th>Lieu Fabrication</th>";
                                echo "<th>Description Étape</th>";
                                echo "<th>Statut</th>";
                                echo "<th>Date Entrée</th>";
                                echo "<th>Date Sortie</th>";
                                echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                
                                while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                    echo "<tr>";
                                    
                                    if ($type_recherche === 'enfant') {
                                        echo "<td>".htmlspecialchars($row['NOM'])."</td>";
                                        echo "<td>".htmlspecialchars($row['PRENOM'])."</td>";
                                    }
                                    
                                    echo "<td>".htmlspecialchars($row['ID_JOUET'])."</td>";
                                    echo "<td>".htmlspecialchars($row['NOM_JOUET'])."</td>";
                                    echo "<td>".htmlspecialchars($row['LIEU_FABRICATION'])."</td>";
                                    echo "<td>".htmlspecialchars($row['DESCRIPTION_ETAPE'])."</td>";
                                    echo "<td>".htmlspecialchars($row['STATUT_FABRICATION'])."</td>";
                                    echo "<td>".htmlspecialchars($row['DATE_ENTREE'])."</td>";
                                    echo "<td>".htmlspecialchars($row['DATE_SORTIE'])."</td>";
                                    echo "</tr>";
                                }
                                
                                echo "</tbody>";
                                echo "</table>";
                            } else {
                                $_SESSION['message'] = "<p class='error'>Erreur lors de la recherche dans l'historique.</p>";
                            }
                            
                            oci_free_statement($stid);
                        }
                        
                        oci_close($idconn);
                    ?>
                </div>

            <?php }else{ ?>
                <!-- MESSAGE SI UTILISATEUR NON CONNECTÉ -->
                <p>Veuillez vous connecter pour accéder à l'historique de fabrication.</p>               
                <p><a href="compte.php">Se connecter</a></p>
            <?php } ?>
        </div>
    </div>
</main>

<script>
    // Gestion de l'affichage des champs de recherche en fonction du type sélectionné
    document.getElementById('type_recherche').addEventListener('change', function() {
        var type = this.value;
        document.getElementById('champ-jouet').style.display = 'none';
        document.getElementById('champ-enfant').style.display = 'none';
        
        if (type === 'jouet') {
            document.getElementById('champ-jouet').style.display = 'block';
        } else if (type === 'enfant') {
            document.getElementById('champ-enfant').style.display = 'block';
        }
    });
</script>

<?php
    // Inclusion du pied de page administrateur
    include("../includes/administrator-footer.php");
?>