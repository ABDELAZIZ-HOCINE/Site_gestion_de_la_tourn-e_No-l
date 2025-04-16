<body>
    <!--########################################################################################################################-->
    <!-- creation des étoiles ---------------------------------------------------------------------------------------------------->
    <div class="stars-container"></div>

    <header>
        <!--########################################################################################################################-->
        <!-- bare de navigation ------------------------------------------------------------------------------------------------------>
        <nav class="navigation">
            <div class="navigation-list">
                <div class="container-logo-title">
                    <div class="container-logo">
                        <div class="logo">
                            <img src="images/img/logo.png" alt="logo">
                        </div>
                    </div>
                    <div class="container-title">
                        <div class="title-text">
                            <h1>SantaLogistics</h1>
                        </div>
                    </div>
                </div>

                <form method="post" action="">
                    <div class="search-bar">
                        <input type="search" id="pageSelector" name="search" list="suggestions" placeholder="Rechercher..." />
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
			    oci_close($idconn); // Ferme la connexion

			} catch (Exception $e) {
			    echo "Erreur : " . $e->getMessage();
			    exit;
			}
			?>
                        <datalist id="suggestions">
                            <?php 
                            if (!empty($results)) {
                                foreach ($results as $row) {
                                    // Ajout des jouets comme options
                                    echo '<option value="' . htmlspecialchars($row['NOM_JOUET']) . '">' . htmlspecialchars($row['NOM_JOUET']) . '</option>';
                                }
                            }
                            ?>
                            <!-- Options pour les pages générales -->
                            <option value="Accueil"></option>
                            <option value="Jouets"></option>
                            <option value="Listes de Souhaits"></option>
                            <option value="Support"></option>
                        </datalist>

                        <button type="submit" name="research">
                            <img src="images/icons/chercher.png" alt="Chercher">
                        </button>
                    </div>
                </form>

                <?php
                    if (isset($_POST['research'])) {
                        $search = htmlspecialchars($_POST['search']); // Récupère et sécurise l'entrée utilisateur

                        // Liste des pages générales
                        $pages = [
                            "Accueil" => "index.php",
                            "Jouets" => "jouets.php",
                            "Listes de Souhaits" => "listes_souhaits.php",
                            "Historique de commandes" => "historique_commandes.php",
                            "Mon Compte" => "compte.php",
                            "Support" => "support.php"
                        ];

                        // Vérifie si l'utilisateur a sélectionné une page générale
                        if (array_key_exists($search, $pages)) {
                            echo '<script>window.location.href = "' .  $pages[$search] . '";</script>'; // Redirige vers la page correspondante
                            exit;
                        }

                        // Si ce n'est pas une page générale, on suppose que c'est un jouet
                        try {
                            // Connexion à la base de données Oracle avec oci_connect
                            $idconn = connexoci("my-param", "oracle2");

                            // Requête pour vérifier si le jouet existe dans la base
                            $requete = "SELECT NOM_JOUET FROM Les_jouets WHERE NOM_JOUET = :nom_jouet";

                            // Préparation de la requête
                            $stmt = oci_parse($idconn, $requete);

                            // Liaison du paramètre :nom_jouet
                            oci_bind_by_name($stmt, ':nom_jouet', $search);

                            // Exécution de la requête
                            oci_execute($stmt);

                            // Récupération du résultat
                            $result = oci_fetch_assoc($stmt);

                            if ($result) {
                            // Si le jouet existe, redirige vers sa page de détails
                            header("Location: jouets.php");
                            exit;
                            } else {
                            // Si le jouet n'existe pas, affiche un message d'erreur
                            echo "<p></p>";
                            }

                            // Libération des ressources
                            oci_free_statement($stmt); // Libère la ressource du statement
                            oci_close($idconn); // Ferme la connexion

                        } catch (Exception $e) {
                            echo "Erreur : " . $e->getMessage();
                        }
			        }
			    ?>
                <div class="nav2">
                    <ul class="elms_nav">
                        <li><a href="index.php" class="menu-item">Accueil</a></li>
                        <li><a href="jouets.php" class="menu-item">Jouets</a></li>
                        <li><a href="listes_souhaits.php" class="menu-item">Listes de Souhaits</a></li>
                        <div class="slash"><p>/</p></div>
                        <li><a href="support.php" class="menu-item">Support</a></li>
                    </ul>
                    <div class="sep"></div>
                    <button class="navigation-button-close" onclick="toggleNav()"><img src="images/icons/close-menu.png" alt="menu"></button>
                </div>
                
                <!-- Bouton pour faire défiler vers le haut -->
                <button class="navigation-button-open" onclick="toggleNav()"><img src="images/icons/open-menu.png" alt="menu"></button>
            </div>
            <div class="sep1"></div>
        </nav>
    </header>
