# Site_gestion_de_la_tourn-e_Noel

# 🎄 Site Web de Commande de Jouets – Projet de Noël---------------------------------------------------------------------------------
Ce projet est un site web destiné pour gerer les commandes de jouets de la tournée Noël par la société Chris Kindle, (SantaLogistics).
Il est divisé en deux parties : une interface utilisateur pour les enfants et une 
interface administrateur pour les lutins.

!!!!!!!!!!!!!!!!!
!!!!!!!!!!!!!!!!!
✅✅✅✅✅✅✅

Il faut s'inscrire en tant que lutin en remplissant le formulaire d'inscription et en utilisant le code administrateur qui est : 0000 
dans le champs code pour accéder a la base de données.

ou bien se connecter directement avec les cordonnées de lutin suivant :
 
  login: test
  password: test

!!!!!!!!!!!!!!!!!
!!!!!!!!!!!!!!!!!


## 🌟 Fonctionnalités principales----------------------------------------------------------------------------------------------------

### 👶 Partie Utilisateur
- Accessible via : `HOST/index.php`
- Permet aux enfants de :
  - Parcourir une liste de jouets
  - Passer une commande à destination du Père Noël

### 🧕 Partie Administrateur (Lutins)
- Accessible via : `HOST/a/index.php`
- Réservée aux lutins (authentification recommandée)
- Fonctionnalités :
  - Gérer les jouets (ajout, suppression, mise à jour)
  - Consulter les commandes passées par les enfants
  - Organiser la tournée de Noël



## 🔐 Connexion à la base de données
Le fichier de configuration de la base de données se trouve à :
```bash
public-html/connexion-base/myparam.inc.php
```

### Contenu du fichier `myparam.inc.php` :
```
<?php
  define("MYHOST","HOST");
  define("MYUSER","USER");
  define("MYPASS","DBNAME");
?>
```

## 📁 Structure du projet
```
public-html/
│
├── index.php # Page d’accueil (interface enfant)
|    ...              
├── a/    # Interface admin (lutins)
│   └── index.php
|   └── ...      
├── connexion-base/
│   └── myparam.inc.php      # Paramètres de connexion à la BDD
```

## ✅ Pré-requis
- Serveur web (Apache ou autre)
- PHP (8.x recommandé)
- Base de données Oracle
- Navigateur moderne (Ex: `Google Chrome`, ... )


