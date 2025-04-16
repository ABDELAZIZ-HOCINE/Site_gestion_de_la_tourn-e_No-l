<?php
    session_start();
    // Inclusion des paramètres de connexion
    include_once("../connexion-base/connex.inc.php");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-nav.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-admi-windows.css">
    <link rel="stylesheet" type="text/css" href="../css/style-of-my-web-site-inscription.css">
    <link rel="icon" type="image/x-icon" href="../images/icons/icon.png">
    <title>SantaLogistics</title>
</head>
<?php
    // Inclusion de l'en-tête
    include_once("../includes/administrator-header.php");
    include_once("../includes/functions.php")
?>


<main>
    <!--########################################################################################################################-->
    <div id="compte" class="home-container">
        <div class="inner1-home-container">
            <h2>Formulaire d'Inscription</h2>
            <div class="sep2"></div>
            <!-- Formulaire d'inscription -->
            <form  id='inscription' action="" method="POST">
                <div><label for="login">Login :</label><input type="text" id="login" name="login" required maxlength="16"></div>
                <div><label for="password">Mot de passe :</label><input type="password" id="password" name="password" required maxlength="64"></div>
                <div><label for="confirm_password">Confirmez le mot de passe :</label><input type="password" id="confirm_password" name="confirm_password" required maxlength="64"></div>
                <div><label for="code">Code :</label><input type="password" id="code" name="code" required maxlength="4"></div>
                <button type="submit" name="inscription">S'inscrire</button>
            </form>
            <p>Déjà inscrit ? <a href="compte.php">Connectez-vous ici</a>.</p>
            <?php
                // Traitement du formulaire d'inscription
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
                    // Récupération et sécurisation des données du formulaire
                    $login = htmlspecialchars($_POST['login'])??'';
                    $password = htmlspecialchars($_POST['password'])??'';
                    $confirm_password = htmlspecialchars($_POST['confirm_password'])??'';
                    $code = htmlspecialchars($_POST['code'])??'';

                    // Vérification du code initial
                    if ($code !== '0000') {
                        echo "Le code est incorrect.";
                        exit();
                    } elseif ($password !== $confirm_password) {
                        // Vérification que les mots de passe correspondent
                        echo "Les mots de passe ne correspondent pas.";
                        exit();
                    } else {
                        try {
                            // Connexion à la base de données Oracle
                            $idconn = connexoci("my-param", "oracle2");
                            // Vérification si le login existe déjà
                            $check_query = "SELECT COUNT(*) AS COUNT FROM lutins WHERE LOGIN = :login";
                            $stmt_check = oci_parse($idconn, $check_query);
                            oci_bind_by_name($stmt_check, ":login", $login);
                            oci_execute($stmt_check);
                            if (!oci_execute($stmt_check)) {
                                $e = oci_error($stmt_check);
                                throw new Exception("Erreur vérification login : ".$e['message']);
                            }
                            $result_check = oci_fetch_assoc($stmt_check);
                            // Libération des ressources
                            oci_free_statement($stmt_check);

                            if ($result_check['COUNT'] != 0) {
                                // Affichage d'un message d'erreur si le login est déjà utilisé
                                echo "<div class='error'>Ce login est déjà utilisé.</div>";
                                exit();
                            } else {
                                // Hachage du mot de passe
                                $hashed_password = hash('sha256', $password);
                                // Génération de l'ID Lutin
                                $id_lutin=genererIdLutin($idconn);
                                // Insertion dans la base de données
                                $insert_query = "INSERT INTO lutins (ID_LUTIN, LOGIN, PASSWORD) VALUES (:id_lutin, :login, :password)";
                                $stmt_insert = oci_parse($idconn, $insert_query);
                                oci_bind_by_name($stmt_insert, ":id_lutin",$id_lutin);
                                oci_bind_by_name($stmt_insert, ":login", $login);
                                oci_bind_by_name($stmt_insert, ":password", $hashed_password);

                                if (oci_execute($stmt_insert)) {
                                    oci_commit($idconn); // Validation de la transaction
                                    echo "<div class='success'>Inscription réussie ! Vous pouvez maintenant vous connecter.</div<";
                                    exit();
                                } else {
                                    // Affichage d'un message d'erreur en cas d'échec de l'insertion
                                    echo "<div class='error'>Erreur lors de l'inscription. Veuillez réessayer.</div>";
                                    exit();
                                }
                                
                                // Libération des ressources
                                oci_free_statement($stmt_insert);
                            }
                            oci_close($idconn);
                        } catch (Exception $e) {
                            // Gestion des erreurs de connexion à la base de données
                            $error = "Erreur de connexion à la base de données : " . $e->getMessage();
                        }
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
