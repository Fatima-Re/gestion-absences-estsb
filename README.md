# Gestion des Absences ESTSB

Une application web complète de gestion des absences pour l'École Supérieure de Technologie de Sidi Bennour (EST-SB), développée avec Laravel 12.

## 🚀 Fonctionnalités

### Pour les Administrateurs
- ✅ Gestion complète des utilisateurs (étudiants, enseignants)
- ✅ Gestion des groupes et modules
- ✅ Gestion des séances de cours
- ✅ Consultation et export des absences
- ✅ Gestion des justifications d'absence
- ✅ Statistiques et rapports détaillés
- ✅ Configuration système
- ✅ Import/Export des données

### Pour les Enseignants
- ✅ Consultation de l'emploi du temps
- ✅ Prise de présence en temps réel
- ✅ Gestion des absences et justifications
- ✅ Génération de rapports par module
- ✅ Suivi statistique des présences

### Pour les Étudiants
- ✅ Consultation des absences personnelles
- ✅ Soumission de justifications avec pièces jointes
- ✅ Suivi de l'assiduité par module
- ✅ Téléchargement de relevés d'absence
- ✅ Gestion des notifications

## 🛠️ Technologies Utilisées

- **Backend**: Laravel 12 (PHP 8.2+)
- **Base de données**: MySQL
- **Frontend**: Bootstrap 5, jQuery, CSS3
- **Exports**: Laravel Excel (XLSX), DOMPDF (PDF)
- **Authentification**: Middleware personnalisé par rôles (`CheckRole`), routes web dans `routes/web.php`
- **Architecture**: MVC Laravel (contrôleurs par rôle sous `app/Http/Controllers/`)

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 5.7+
- Node.js & npm (pour les assets)
- XAMPP/WAMP ou serveur web équivalent

## 🔧 Installation

### 1. Clonage du projet
```bash
git clone https://github.com/Fatima-Re/gestion-absences-estsb.git
cd gestion-absences-estsb
```

### 2. Installation des dépendances
```bash
composer install
npm install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configuration de la base de données
Modifiez le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=gestion_absences-estsb
DB_USERNAME=votre_username
DB_PASSWORD=votre_password
```

### 5. Migration et seeding
```bash
php artisan migrate
php artisan db:seed
```

Le fichier [`database/migrations/2026_03_27_120000_align_database_with_application_models.php`](database/migrations/2026_03_27_120000_align_database_with_application_models.php) aligne le schéma (séances, présences par étudiant, absences/justifications, notifications, groupes, modules) sur les modèles Eloquent utilisés par l’application. Après une mise à jour du dépôt, exécutez toujours `php artisan migrate` sur votre base.

**Données de démo** : `php artisan db:seed` appelle [`DemoDataSeeder`](database/seeders/DemoDataSeeder.php) (additif : n’efface pas les données existantes). Des comptes supplémentaires `@demo.local` / mots de passe `demo123` peuvent être créés en plus des comptes ESTSB ci-dessous.

### 6. Compilation des assets
```bash
npm run build
# ou pour le développement
npm run dev
```

### 7. Démarrage du serveur
```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## 👥 Comptes de démonstration

Après le seeding, les comptes suivants sont disponibles :

### Administrateur
- **Email**: admin@estsb.ma
- **Mot de passe**: admin123

### Enseignants
- **Dr. Fatima Alaoui**: fatima.alaoui@estsb.ma / teacher123
- **Pr. Mohamed Bennani**: mohamed.bennani@estsb.ma / teacher123
- **Dr. Rachid Tazi**: rachid.tazi@estsb.ma / teacher123

### Étudiants
- **Ahmed Bennani**: ahmed.bennani@estsb.ma / student123
- **Sara Alaoui**: sara.alaoui@estsb.ma / student123
- **Youssef Tazi**: youssef.tazi@estsb.ma / student123
- **Fatima Zahra**: fatima.zahra@estsb.ma / student123

## 📁 Structure du Projet

```
gestion-absences-estsb/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Contrôleurs administrateur
│   │   ├── Teacher/        # Contrôleurs enseignant
│   │   ├── Student/        # Contrôleurs étudiant
│   │   └── Auth/           # Authentification
│   ├── Models/             # Modèles Eloquent
│   ├── Exports/            # Classes d'export Excel
│   └── Providers/          # Service providers
├── database/
│   ├── migrations/         # Migrations base de données
│   └── seeders/           # Seeders de données
├── public/                 # Assets publics
├── resources/
│   ├── views/             # Templates Blade
│   │   ├── admin/         # Vues administrateur
│   │   ├── teacher/       # Vues enseignant
│   │   ├── student/       # Vues étudiant
│   │   ├── layouts/       # Layouts principaux
│   │   └── partials/      # Composants réutilisables
│   ├── css/               # Styles personnalisés
│   └── js/                # JavaScript
├── routes/
│   └── web.php            # Routes de l'application
└── tests/                 # Tests unitaires et fonctionnels
```

## 🎯 Utilisation

### Gestion des Utilisateurs (Admin)
1. Accédez à l'espace administrateur
2. Allez dans "Utilisateurs" > "Gérer les utilisateurs"
3. Créez, modifiez ou désactivez des comptes
4. Utilisez l'import en masse pour ajouter plusieurs étudiants

### Prise de Présence (Enseignant)
1. Consultez votre emploi du temps
2. Cliquez sur une séance pour prendre la présence
3. Marquez les étudiants présents/absents
4. Modifiez si nécessaire avant la deadline

### Consultation des Absences (Étudiant)
1. Accédez à votre tableau de bord étudiant
2. Consultez vos absences par module
3. Soumettez des justifications avec documents
4. Téléchargez vos relevés d'absence

## 📊 Exports et Rapports

L'application supporte plusieurs formats d'export :

- **Excel (.xlsx)**: Listes d'étudiants, rapports d'absence
- **PDF**: Relevés individuels, statistiques
- **JSON**: Sauvegarde des paramètres système

## 🔒 Sécurité et production

- Authentification basée sur les rôles
- Middleware de protection des routes
- Validation des données côté serveur
- Protection CSRF sur tous les formulaires
- Mots de passe hashés avec bcrypt
- Limitation du débit sur `POST /login` (throttle) pour limiter les tentatives de connexion

**Mise en production (recommandations)** : définir `APP_DEBUG=false`, configurer le mail (`MAIL_*`) pour la réinitialisation du mot de passe, servir l’application en HTTPS, planifier des sauvegardes régulières de la base MySQL et des fichiers uploadés (`storage/app/public`). Si vous utilisez des files d’attente ou des notifications par mail, lancez un worker : `php artisan queue:work` (tables `jobs` / `failed_jobs` présentes).

**Étudiants sans groupe** : un étudiant non affecté à un groupe (`group_id` vide) voit un emploi du temps et des statistiques de séances vides jusqu’à affectation par un administrateur.

## 🧪 Tests

```bash
# Exécuter tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## Routes

L’application est exposée via **routes web** uniquement (pas d’API REST dédiée dans ce dépôt). Voir [`routes/web.php`](routes/web.php) pour les préfixes `admin`, `teacher` et `student`.

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Développeurs

- **Fatima Ezzahra REBBOUH** - *Développement initial* - [Fatima-Re](https://github.com/Fatima-Re)
- **Encadrant**: Badreddine CHERKAOUI

## 🙏 Remerciements

- École Supérieure de Technologie de Sidi Bennour
- Framework Laravel
- Communauté open source

---

**Note**: Cette application est développée dans le cadre d'un projet de fin d'études (PFE) et est destinée à un usage éducatif.
