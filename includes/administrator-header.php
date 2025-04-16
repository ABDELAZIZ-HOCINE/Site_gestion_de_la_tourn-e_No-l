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
                            <img src="../images/img/logo.png" alt="logo">
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
                        <datalist id="suggestions">

                            <!-- Options pour les pages générales -->
                            <option value="Tableaux de bord"></option>
                            <option value="Mon Compte"></option>
                            <option value="Support"></option>
                            <!-- Options pour les autres pages -->
                            <option value="Les enfants"></option>
                            <option value="Les elfes"></option>
                            <option value="Les rennes"></option>
                            <option value="Les équipes de fabrication"></option>
                            <option value="Les équipes logistiques"></option>
                            <option value="Chef d'équipe"></option>
                            <option value="Les matières premières"></option>
                            <option value="Les fournisseurs"></option>
                            <option value="Les ateliers"></option>
                            <option value="Les entrepôts"></option>
                            <option value="Les spécialités"></option>
                            <option value="Les jouets"></option>
                            <option value="Les cadeaux"></option>
                            <option value="Substitution des jouets"></option>
                            <option value="L'historique de fabrication"></option>
                            <option value="Commande jouets"></option>
                            <option value="Commande matière première"></option>
                            <option value="Commande confiée au ST"></option>
                            <option value="Table des absences"></option>
                            <option value="Les itinéraires de distribution"></option>
                            <option value="Les livraisons"></option>
                            <option value="Les traîneaux"></option>
                        </datalist>

                        <button type="submit" name="research">
                            <img src="../images/icons/chercher.png" alt="Chercher">
                        </button>
                    </div>
                </form>

                <?php
                    if (isset($_POST['research'])) {
                        $search = htmlspecialchars($_POST['search']); // Récupère et sécurise l'entrée utilisateur
                        // Liste des pages générales
                        $pages = [
                            "Tableaux de bord" => "index.php",
                            "Mon Compte" => "compte.php",
                            "Support" => "support.php",
                            "Les enfants" => "gestion_enfants.php",
                            "Les elfes" => "gestion_elfes.php",
                            "Les rennes" => "gestion_rennes.php",
                            "Les équipes de fabrication" => "gestion_equipes_fabrication.php",
                            "Les équipes logistiques" => "gestion_equipes_logistiques.php",
                            "Chef d'équipe" => "gestion_chef_equipe.php",
                            "Les matières premières" => "gestion_matieres_premieres.php",
                            "Les fournisseurs" => "gestion_fournisseurs.php",
                            "Les ateliers" => "gestion_ateliers.php",
                            "Les entrepôts" => "gestion_entrepots.php",
                            "Les spécialités" => "gestion_specialites.php",
                            "Les jouets" => "gestion_jouets.php",
                            "Les cadeaux" => "gestion_cadeaux.php",
                            "Substitution des jouets" => "gestion_substituer_par.php",
                            "L'historique de fabrication" => "gestion_historique.php",
                            "Commande jouets" => "gestion_commande_jouets.php",
                            "Commande matière première" => "gestion_commande.php",
                            "Commande confiée au ST" => "gestion_confie_au.php",
                            "Table des absences" => "gestion_remplace_elfe.php",
                            "Les itinéraires de distribution" => "gestion_itineraires.php",
                            "Les livraisons" => "gestion_livraisons.php",
                            "Les traîneaux" => "gestion_traineaux.php"
                        ];
                        // Vérifie si l'utilisateur a sélectionné une page générale
                        if (array_key_exists($search, $pages)) {
                            echo '<script>window.location.href = "' .  $pages[$search] . '";</script>'; // Redirige vers la page correspondante
                            exit;
                        }
			        }
			    ?>
                <div class="nav2">
                    <ul class="elms_nav">
                        <li><a href="index.php" class="menu-item">Tableaux de bord</a></li>
                        <li><a href="compte.php" class="menu-item">Mon Compte</a></li>
                        <div class="slash"><p>/</p></div>
                        <li><a href="support.php" class="menu-item">Support</a></li>
                    </ul>
                    <div class="sep"></div>
                    <button class="navigation-button-close" onclick="toggleNav()"><img src="../images/icons/close-menu.png" alt="menu"></button>
                </div>
                
                <!-- Bouton pour faire défiler vers le haut -->
                <button class="navigation-button-open" onclick="toggleNav()"><img src="../images/icons/open-menu.png" alt="menu"></button>
            </div>
            <div class="sep1"></div>
        </nav>
    </header>
