<?php
function connexoci($param, $base)
{
	$charset = 'AL32UTF8';
    // Inclusion du fichier de configuration contenant les constantes de connexion
    include_once($param . ".inc.php");

    try {
        // Connexion à la base de données Oracle avec oci_connect
        $conn = oci_connect(MYUSER, MYPASS, MYHOST,$charset);

        // Vérification de la connexion
        if (!$conn) {
            $e = oci_error(); // Récupère l'erreur Oracle
            throw new Exception("Connexion impossible à la base $base : " . $e['message']);
        }

        return $conn; // Retourne la connexion

    } catch (Exception $e) {
        // Affichage d'une alerte en cas d'erreur
        echo "<script type='text/javascript'>";
        echo "alert('" . addslashes($e->getMessage()) . "');";
        echo "</script>";
        return null; // Retourne null en cas d'échec
    }
}
?>
