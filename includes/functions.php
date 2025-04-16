<?php
    function getLast8Chars($string) {
        if (preg_match('/.{8}$/', $string, $matches)) {
            return $matches[0];
        }
        return '';
    }

    // Fonction pour générer un ID unique
    function genererIdLutin($idconn){
        $id_lutin = getLast8Chars(uniqid());
        $check_query = "SELECT COUNT(*) AS COUNT FROM lutins WHERE ID_LUTIN = :ID_LUTIN";
        $stmt_check = oci_parse($idconn, $check_query);
        oci_bind_by_name($stmt_check, ":ID_LUTIN", $id_lutin);
        oci_execute($stmt_check);

        if (!oci_execute($stmt_check)) {
            $e = oci_error($stmt_check);
            throw new Exception("Erreur vérification login : ".$e['message']);
        }

        $result_check = oci_fetch_assoc($stmt_check);
        oci_free_statement($stmt_check);

        if ($result_check['COUNT'] != 0){
            // Si l'ID existe déjà, on en génère un nouveau
            genererIdLutin($idconn);
        }else{
            return $id_lutin;
        }
    }
?>
<?php
function afficherInformationsLutin()
{?>
    <!-- Affichage d'un message de bienvenue si l'utilisateur est connecté -->
    <p>Bienvenue <b> <?php echo htmlspecialchars($_SESSION['user_name']); ?></b> dans votre compte perssonnel !</p>
    <div class="user-actions">
        <h3>Informations personnelles</h3>
        <fieldset>
            <?php
            // Connexion à la base de données Oracle
            $idconn = connexoci("my-param", "oracle2");

            // Requête SQL pour récupérer les informations de l'utilisateur connecté
            $requete = "SELECT id_lutin, nom, prenom, login, password FROM lutins WHERE id_lutin = :id_lutin";
            
            // Préparation et exécution de la requête
            $stmt = oci_parse($idconn, $requete);
            $id_lutin = $_SESSION['user_id'];
            oci_bind_by_name($stmt, ":id_lutin", $id_lutin);
            oci_execute($stmt);
            $user = oci_fetch_assoc($stmt);
            ?>
            
            <?php if ($user){ ?>
            <!-- Affichage des informations de l'utilisateur dans un formulaire -->
            <div class="modifier_cust_info">
                <form id="infos" method="POST">
                    <table>
                        <tr><td>ID Lutin:</td><td><?php echo htmlspecialchars($user['ID_LUTIN']); ?></td><td></td></tr>
                        <tr><td>Nom:</td><td><?php echo htmlspecialchars($user['NOM']??'null'); ?></td><td><a href="modifier_profil.php?champ=NOM" class="action-button">Modifier</a></td></tr>
                        <tr><td>Prénom:</td><td><?php echo htmlspecialchars($user['PRENOM']??'null'); ?></td><td><a href="modifier_profil.php?champ=PRENOM" class="action-button">Modifier</a></td></tr>
                        <tr><td>Identifiant:</td><td><?php echo htmlspecialchars($user['LOGIN']); ?></td><td><a href="modifier_profil.php?champ=LOGIN" class="action-button">Modifier</a></td></tr>
                        <tr><td>Mot de passe:</td><td><?php echo '****************'; ?></td><td><a href="modifier_profil.php?champ=PASSWORD" class="action-button">Modifier</a></td></tr>
                    </table>
                    <ul>
                        <li><input type="submit" name="valide" id="valide" value="Valider les modifications" class="btn"></li>
                        <li><input type="submit" name="logout" id="logout" value="Déconnexion" class="btn"></li>
                        <li><input type="submit" name="delete" id="delete" value="Supprimer mon compte" class="btn"></li>
                    </ul>

                    <?php
                        if (isset($_POST['delete'])){
                    ?>
                    <form id="infos" method="POST">
                        <!-- Modal de confirmation -->
                        <div id="confirmationModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; display:flex; justify-content:center; align-items:center;">
                            <div style="background:white; padding:20px; border-radius:5px; max-width:400px; text-align:center;">
                                <h3>Confirmation requise</h3>
                                <p>Pour confirmer, tapez "<strong style="color:red">SUPPRIMER</strong>" :</p>
                                <input type="text" id="confirmationInput" name="confirm_supp"
                                    style="width:100%; padding:10px; margin:15px 0; font-size:16px;"
                                    placeholder="SUPPRIMER">
                                <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                                    <input type="submit" name="annuler" value="Annuler"
                                            style="padding:10px 20px; background:#6c757d; color:white; border:none; cursor:pointer;">
                                    <input type="submit" name="confirm_supp_btn" value="Confirmer"
                                            style="padding:10px 20px; background:#dc3545; color:white; border:none; cursor:pointer;">
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                        }
                    ?>


                    <?php 
                        if (isset($_SESSION['message'] )){
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                        }
                    ?>
                </form>
            </div>
            <?php 
            }
            // Libération des ressources et fermeture de la connexion à la base de données
            oci_free_statement($stmt);
            oci_close($idconn);
            ?>
        </fieldset>
    </div>
<?php } ?>