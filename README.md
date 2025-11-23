# 📚 LibraShelf

Bienvenue sur **LibraShelf**, une application web moderne développée avec **Symfony 7** pour la gestion complète d'une bibliothèque (ouvrages, exemplaires, emprunts, réservations).

---

## 🌟 Fonctionnalités

### 🎭 Gestion des Rôles & Sécurité

L'application gère trois types d'utilisateurs avec des interfaces et des droits distincts :

#### 👤 Membre (`ROLE_MEMBER`)
- Accès au catalogue en ligne
- Recherche avancée (Titre, Auteur, Catégorie, Langue, Année)
- Consultation des fiches détaillées des livres
- Réservation intelligente :
    - Emprunt direct si un exemplaire est disponible
    - Inscription sur liste d’attente si aucun exemplaire n’est libre
- Espace personnel **Mes Réservations** :
    - Suivi des emprunts en cours (avec décompte des jours restants)
    - Historique des lectures passées
    - Possibilité de rendre un livre ou d’annuler une demande en attente

#### 📚 Libraire (`ROLE_LIBRARIAN`)
- Gestion complète du catalogue (**CRUD Ouvrages**)
- Gestion du stock physique (Ajout/Modification d’exemplaires)
- Attribution automatique des exemplaires aux membres sur liste d’attente lors de l’ajout de stock
- Gestion des auteurs, catégories et tags

#### 🛡️ Administrateur (`ROLE_ADMIN`)
- Tous les droits du Libraire
- Accès au panneau de configuration :
    - Réglage de la durée d’emprunt (ex : 30 jours)
    - Réglage du quota de livres par personne (ex : 3 livres max)
    - Fixation des pénalités de retard

---

## 🚀 Points Forts Techniques

- **Interface Moderne** : Bootstrap 5 avec design soigné (Cartes, Badges, Icônes Bootstrap)
- **Formulaires Avancés** :
    - Champs *Floating Labels*
    - Listes déroulantes intelligentes avec recherche
    - Accordéons pour organiser les formulaires longs
- **Logique Métier Complexe** :
    - Algorithme d’attribution automatique des retours (le livre passe directement de main en main si une file d’attente existe)
    - Calcul dynamique des dates de retour
    - Protection anti-doublon et respect des quotas

---

## ⚙️ Pré-requis

Avant de commencer, assurez-vous d’avoir installé :

- PHP 8.1 ou supérieur
- Composer
- Symfony CLI (recommandé)
- PostgreSQL (ou MySQL)

---

## 💻 Installation

Cloner le dépôt :

```bash
    git clone https://github.com/votre-pseudo/librashelf.git
    cd librashelf
```

Installer les dépendances PHP :
```bash
  composer install
```

Configurer l’environnement : Créez un fichier .env.local à la racine pour vos identifiants de base de données.
```
    # .env.local
    DATABASE_URL="postgresql://user:password@127.0.0.1:5432/librashelf?serverVersion=16&charset=utf8"
```

Créer la base de données et les tables :
```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
```

Charger les données de test (Fixtures) : Cette commande va créer les utilisateurs par défaut et générer des centaines de livres et d’auteurs réalistes.
```bash
    php bin/console doctrine:fixtures:load
```
---
## 🏃‍♂️ Lancer le serveur

Démarrez le serveur web local de Symfony :
```bash
    symfony server:start
```
Accédez ensuite à http://127.0.0.1:8000.

---

## 👤 Comptes de Démonstration

Les fixtures créent automatiquement ces comptes pour tester tous les rôles :


| Rôle       | Email                  | Mot de passe |
|------------|------------------------|--------------|
| Admin      | admin1@gmail.com       | admin1       |
| Libraire   | librarian1@gmail.com   | librarian1   |
| Membre     | member1@gmail.com      | member1      |

---

## 📂 Structure du Projet

    src/Controller/

        CatalogController.php : Espace Membre (Recherche, Réservation)

        OuvrageController.php : Espace Libraire (Gestion des livres)

        AdminController.php : Espace Admin (Paramètres)

    src/Entity/

        Modèles de données (Ouvrage, Exemplaire, Reservation, Personne, Configuration...)

    src/Form/

        Formulaires (OuvrageType, OuvrageFilterType, ConfigurationType...)

    templates/

        Vues Twig avec design Bootstrap
