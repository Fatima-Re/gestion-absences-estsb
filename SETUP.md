# Setup Guide for Gestion des Absences ESTSB

This guide will help you set up the application for testing.

## Prerequisites

- PHP 8.2+
- MySQL 5.7+
- Composer
- Node.js & npm
- XAMPP/WAMP (for local development)

## Quick Setup

### 1. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

```bash
# Create database (run in MySQL)
CREATE DATABASE gestion_absences_estsb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Update .env with your database settings
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=gestion_absences_estsb
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 4. Database Migration & Seeding

```bash
# Run migrations
php artisan migrate

# Seed with test data
php artisan db:seed
```

### 5. Compile Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 6. Storage Setup

```bash
# Create storage link
php artisan storage:link
```

### 7. Start Application

```bash
# Start development server
php artisan serve
```

Visit http://localhost:8000 in your browser.

## Test Accounts

After seeding, these accounts are available:

### Admin
- **Email**: admin@estsb.ma
- **Password**: admin123

### Teachers
- **Dr. Fatima Alaoui**: fatima.alaoui@estsb.ma / teacher123
- **Pr. Mohamed Bennani**: mohamed.bennani@estsb.ma / teacher123

### Students
- **Ahmed Bennani**: ahmed.bennani@estsb.ma / student123
- **Sara Alaoui**: sara.alaoui@estsb.ma / student123

## Testing Checklist

### Environment & Setup Testing ✅
- [x] PHP 8.2+ compatibility verified
- [x] MySQL database connection confirmed
- [x] Laravel 12 installation and dependencies installed
- [x] Composer and npm packages installed
- [x] Asset compilation working
- [x] .env configuration properly set
- [x] Application startup successful

### Database & Migration Testing ✅
- [x] Database migrations run successfully
- [x] Database seeding with sample data completed
- [x] All tables created correctly
- [x] Model relationships tested
- [x] Default settings properly seeded
- [x] Data integrity constraints verified

### Authentication & Authorization Testing
- [ ] Test admin login with credentials
- [ ] Test teacher login with credentials
- [ ] Test student login with credentials
- [ ] Test invalid credentials rejection
- [ ] Test account lockout for disabled users
- [ ] Test password reset functionality
- [ ] Test "Remember Me" functionality
- [ ] Verify role-based access control
- [ ] Test unauthorized access attempts

## Troubleshooting

### Common Issues

1. **Missing views error**: Fixed - all authentication views created
2. **Database connection error**: Check .env database settings
3. **Migration errors**: Ensure database exists and has proper permissions
4. **Asset compilation errors**: Run `npm install` and check Node.js version

### Reset Setup

If you encounter issues, you can reset:

```bash
# Drop and recreate database
php artisan migrate:fresh --seed

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Next Steps

Once setup is complete, proceed with the comprehensive testing checklist provided in the README.md file.