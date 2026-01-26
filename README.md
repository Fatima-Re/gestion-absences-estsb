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
- **Authentification**: Middleware personnalisé par rôles
- **Architecture**: MVC avec repositories et services

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

## 🔒 Sécurité

- Authentification basée sur les rôles
- Middleware de protection des routes
- Validation des données côté serveur
- Protection CSRF sur tous les formulaires
- Mots de passe hashés avec bcrypt

## 🧪 Tests

```bash
# Exécuter tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📝 API Documentation

Les routes API principales :

```
GET    /api/users              # Liste des utilisateurs (Admin)
POST   /api/attendance         # Enregistrer présence (Teacher)
GET    /api/absences/{user}    # Absences d'un utilisateur (Student)
POST   /api/justifications     # Soumettre justification (Student)
```

## 🤝 Contribution

1. Fork le projet
2. Créez une branche feature (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Pushez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

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
