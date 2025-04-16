<?php
    session_start();
    // Inclusion des paramètres de connexion
    include_once("../connexion-base/connex.inc.php");
    include_once("../includes/functions.php");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-windows.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-compte.css">
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics</title>
</head>
<?php
    // Inclusion de l'en-tête
    include("../includes/administrator-header.php");
?>


<main>
    <!--########################################################################################################################-->
    <div id="compte" class="home-container">
        <div class="inner1-home-container">
            <h2>Compte Personnel</h2>
            <div class="sep2"></div>
            <!--##########################################################################################################################-->
            <!-- Vérification de l'utilisateur connecté------------------------------------------------------------------------------------->
            <?php if (isset($_SESSION['user_id'])){
                afficherInformationsLutin();
            }else{?>
                <!-- Affichage du formulaire de connexion si l'utilisateur n'est pas connecté -->
                <p>Veuillez vous connecter pour accéder à votre espace personnel.</p>
                <form id='connexion' method="post" action="">
                    <input type="text" name="login"  placeholder="Identifiant" maxlength="16"required>
                    <input type="password" name="password" placeholder="Mot de passe" maxlength="16" required>
                    <button type="submit" name="connexion">Se connecter</button>
                </form>
                <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
            <?php }?>

            <?php
            // Gestion de la validation des modifications
            if (isset($_POST['valide'])) {
                    // Supposons que $idconn est votre connexion Oracle déjà établie
                    try {
                        // Connexion à la base de données Oracle
                        $idconn = connexoci("my-param", "Oracle2");
                        // Vérification de la connexion à la base de données
                        if (!$idconn) {
                            throw new Exception("Erreur de connexion à la base de données");
                        }
                        // Valider la transaction
                        if (oci_commit($idconn)){
                            $_SESSION['message']="<div class='success'>Modification validée avec succès</div>";
                        }else{
                            $_SESSION['message']="<div class='error'>Modification non validee</div>";
                        } 
                    } catch (Exception $e) {
                        // En cas d'erreur, annuler la transaction
                        oci_rollback($idconn);
                        echo "Erreur: " . $e->getMessage();
                    }
                // Redirection vers la même page après déconnexion
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }

            // Gestion de la déconnexion
            if (isset($_POST['logout'])) {
                session_unset();
                session_destroy();
                // Redirection vers la même page après déconnexion
                echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                exit();
            }
            // Gestion de la suppression de compte
            if (isset($_POST['confirm_supp_btn'])) {
                if (!isset($_POST['confirm_supp']) || $_POST['confirm_supp']!="SUPPRIMER"){
                    $_SESSION['message']="<div class='error'>Suppression annulé! Saisissez \"SUPPRIMER\" pour pouvoir valider la suppression </div>";
                    // Redirection vers la même page après déconnexion
                    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                    exit();
                }

                try {
                    $idconn = connexoci("my-param", "Oracle2");   
                    if (!$idconn) {
                        throw new Exception("Erreur de connexion à la base de données");
                    }

                    if (!isset($_SESSION['user_id'])) {
                        throw new Exception("Aucun utilisateur connecté");
                    }

                    $userId = $_SESSION['user_id'];

                    // Correction de la requête DELETE
                    $requete = "DELETE FROM lutins WHERE id_lutin = :user_id";
                    $stmt = oci_parse($idconn, $requete);
                    oci_bind_by_name($stmt, ":user_id", $userId);

                    if (!oci_execute($stmt)) {
                        throw new Exception("Erreur lors de la suppression du compte");
                    }
                    
                    oci_commit($idconn);
                    session_destroy();

                    echo '<script>
                            alert("Votre compte a été supprimé avec succès");
                            window.location.href = "compte.php";
                        </script>';
                    exit();

                } catch (Exception $e) {
                    oci_rollback($idconn);
                    echo '<script>alert("Erreur: '.addslashes($e->getMessage()).'");</script>';
                }
            }

            // Traitement du formulaire de connexion
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['connexion'])) {
                $login = htmlspecialchars($_POST['login']);
                $password = htmlspecialchars($_POST['password']);
                // Vérification des identifiants de connexion
                try {
                    // Connexion à la base de données Oracle
                    $idconn = connexoci("my-param", "Oracle2");
                    // Vérification de la connexion à la base de données
                    if (!$idconn) {
                        throw new Exception("Erreur de connexion à la base de données");
                    }
                    // Requête SQL sécurisée pour récupérer les informations de l'utilisateur
                    $requete = "SELECT id_lutin, nom, prenom, password FROM lutins WHERE login = :login";
                    $stmt = oci_parse($idconn, $requete);
                    // Liaison du paramètre :login avec la valeur de $login
                    oci_bind_by_name($stmt, ":login", $login);
                    // Exécution de la requête
                    if (!oci_execute($stmt)) {
                        throw new Exception("Erreur d'exécution de la requête");
                    }
                    // Récupération des résultats de la requête
                    $user = oci_fetch_assoc($stmt);

                    // Vérification si l'utilisateur existe
                    if (!$user){
                        // Affichage d'un message d'erreur si l'identifiant est incorrect
                        echo "<div class='error'>Identifiant incorrects</div>";
                        exit();
                    }else{
                        // Vérification du mot de passe (SHA-256)
                        $hashedPassword = hash('sha256', $password);
                        
                        // Comparaison du mot de passe hashé avec celui stocké en base de données
                        if ($hashedPassword === $user['PASSWORD']) {
                            // Création de la session avec les informations de l'utilisateur
                            $_SESSION['user_id'] = $user['ID_LUTIN'];
                            $_SESSION['user_name'] = $user['NOM'] . ' ' . $user['PRENOM'];
                            // Redirection vers la même page après connexion réussie
                            echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';
                            exit();
                        } else {
                            // Affichage d'un message d'erreur si le mot de passe est incorrect
                            echo "<div class='error'>Mot de passe incorrects</div>";
                            exit();
                        }
                    }

                    // Nettoyage des ressources
                    oci_free_statement($stmt);
                    oci_close($idconn);

                } catch (Exception $e) {
                    // Gestion sécurisée des erreurs : enregistrement dans les logs
                    error_log("<div class='error'>" . $e->getMessage()."</div>");
                    exit();
                }
            }
            ?>
        </div>
    </div>
</main>

<?php
// Inclusion du pied de page
include("../includes/administrator-footer.php");
?>
