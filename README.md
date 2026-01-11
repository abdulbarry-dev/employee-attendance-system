# Arena Système Pointage

<div align="center">

**A modern, real-time employee attendance tracking system with QR code authentication and geolocation support.**

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-%5E11.0-red.svg)](https://laravel.com/)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](#)

[Features](#features) • [Quick Start](#quick-start) • [Architecture](#architecture) • [Documentation](#documentation) • [Contributing](#contributing)

</div>

---

## Overview

Arena Système Pointage is a sophisticated employee attendance and time-tracking platform designed for modern workplaces. It combines real-time monitoring, secure authentication, and comprehensive reporting capabilities to streamline workforce management.

Built with **Laravel 11**, **Livewire 3**, and **Flux UI**, the system provides a seamless experience for both administrators and employees with a focus on security, reliability, and user experience.

---

## Features

### Security & Authentication

- QR Code Authentication - Secure employee login via office kiosk QR codes
- Two-Factor Authentication - Enhanced security with TOTP-based 2FA
- Geolocation Tracking - Record check-in/check-out location data
- Token-Based Sessions - Tokens marked as used after login to prevent reuse
- Role-Based Access Control - Admin and employee role separation

### Attendance Management

- Real-Time Check-In/Check-Out - Instant time recording
- Break Management - Track lunch and break periods
- Shift Duration Calculation - Automatic work hour computation
- Attendance History - Complete audit trail of employee time records
- Status Dashboard - Live monitoring of employee attendance

### Monitoring & Reporting

- Live Activity Feed - Real-time attendance updates
- Attendance Monitor - Comprehensive view of all employee activities
- Historical Data - Query and export attendance records
- Statistics Dashboard - Present/absent/on-break metrics
- Automated Reports - Scheduled attendance reports

### Employee Management

- Employee Profiles - Manage employee information and contacts
- Account Creation - Automatic welcome emails with setup instructions
- Employee Suspension - Ban/unban functionality
- Access Control - Restrict employee access when needed

### Settings & Customization

- Profile Management - Update personal information
- Password Management - Secure password updates
- Appearance Settings - Light/dark/system theme support
- Recovery Codes - 2FA backup codes for account recovery

### Multi-Language Support

- English interface with translation-ready architecture
- Locale configuration for international deployments

---

## Quick Start

### Prerequisites

- **PHP** 8.2+
- **Composer** 2.0+
- **Node.js** 18+ & npm 9+
- **SQLite** or MySQL (local testing/production)
- **Git**

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/arena-systeme-pointage.git
   cd arena-systeme-pointage
   ```

2. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

3. **Configure environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**

   ```bash
   npm run build
   ```

6. **Start development server**

   ```bash
   php artisan serve
   ```

   Access the application at `http://localhost:8000`

### Default Credentials

After seeding, test with:

- **Admin Email**: `admin@example.com`
- **Password**: Generated during seeding (check console output)

---

## Architecture

### Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend Framework** | Laravel 11 |
| **Frontend Framework** | Livewire 3 + Alpine.js |
| **UI Component Library** | Flux UI |
| **Styling** | Tailwind CSS |
| **Authentication** | Laravel Fortify |
| **Database** | SQLite (dev) / MySQL (prod) |
| **Build Tools** | Vite |
| **Testing** | PHPUnit |

### Directory Structure

```
arena-systeme-pointage/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # API & specialized controllers
│   │   ├── Middleware/       # HTTP middleware
│   │   └── Responses/        # Custom response handlers
│   ├── Livewire/             # Interactive components
│   │   ├── Attendance/       # Attendance features
│   │   ├── Employee/         # Employee management
│   │   └── Settings/         # User settings
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Email notifications
│   ├── Policies/             # Authorization policies
│   └── Providers/            # Service providers
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database schema
│   ├── factories/            # Model factories
│   └── seeders/              # Data seeders
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript files
├── routes/                   # Route definitions
├── tests/                    # Test suites
└── public/                   # Public assets
```

### Key Components

#### Authentication Flow

```
Employee → Scans QR Code → Token Generated → Login Page
    ↓
Validates Token → FortifyServiceProvider → Marks Token Used
    ↓
LoginResponse → Session Created → Dashboard/Punch Pad
```

#### QR Code Token System

- **Token Lifetime**: 5 minutes
- **Auto-Refresh**: QR code refreshes every 30 minutes
- **Token Isolation**: Tokens marked as used prevent session interference
- **Session Persistence**: User sessions independent of token state

#### Token Security

- Tokens generate with random 64-character string
- Marked as used after successful login
- Invalid/expired tokens reject with clear error messages
- IP address logged for audit trail

---

## Configuration

### Environment Variables

**Key settings in `.env`:**

```env
# Application
APP_NAME=Arena Système Pointage
APP_ENV=production          # local|production|testing
APP_DEBUG=false             # Always false in production
APP_URL=https://example.com # Your domain

# Localization
APP_LOCALE=en              # Language (en|fr|etc)
APP_TIMEZONE=UTC           # Server timezone

# Database
DB_CONNECTION=sqlite       # sqlite|mysql|pgsql
DB_DATABASE=database.sqlite

# Email (for notifications)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning          # debug|info|warning|error
```

### Database

The application uses Laravel migrations. Default models:

- **User** - Admin and employee accounts
- **Attendance** - Check-in/check-out records
- **AttendanceBreak** - Break/lunch periods
- **EmployeeLoginToken** - QR code tokens
- **Role & Permission** - Spatie Laravel Permission

---

## Usage

### For Administrators

1. **Access Dashboard**
   - Navigate to `http://localhost:8000/dashboard`
   - View real-time attendance statistics
   - Access quick actions

2. **Manage Employees**
   - Go to `/employees`
   - Create new employee accounts
   - View employee details
   - Ban/unban accounts as needed

3. **Monitor Attendance**
   - View real-time activity feed at `/attendance/monitor`
   - Check attendance history at `/attendance/history`
   - Review employee check-in/out records

4. **Setup Office Kiosk**
   - Access `/office-kiosk`
   - Display QR code on kiosk display
   - QR code auto-refreshes every 30 minutes

### For Employees

1. **Check In/Out**
   - Navigate to `/employee/punch`
   - Click "Check In" to start shift
   - Click "Start Break" for lunch
   - Click "Check Out" to end shift

2. **View Attendance**
   - Check shift duration on punch pad
   - View all recorded breaks

3. **Manage Settings**
   - Update profile at `/profile`
   - Change password at `/user-password`
   - Enable 2FA at `/two-factor`
   - Customize appearance theme

---

## API Reference

### Authentication Endpoints

#### Login with QR Token

```http
POST /login
Content-Type: application/x-www-form-urlencoded

email=employee@example.com&password=password&token=qr_token
```

### Attendance Endpoints

#### Get Attendance Records

```http
GET /api/attendance?date=2026-01-11
Authorization: Bearer token
```

#### Record Check-In

```http
POST /api/attendance/check-in
Content-Type: application/json
Authorization: Bearer token

{
  "latitude": 48.8566,
  "longitude": 2.3522
}
```

---

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code
./vendor/bin/pint

# Analyze code
./vendor/bin/phpstan analyse app
```

### Database Reset

```bash
# Reset and seed database
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback
```

### Asset Building

```bash
# Development (watch mode)
npm run dev

# Production (optimized)
npm run build

# Analyze bundle
npm run build -- --analyze
```

---

## Deployment

### Production Checklist

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Generate APP_KEY if not present
- [ ] Configure email service (SMTP/Sendgrid)
- [ ] Setup SSL certificate
- [ ] Configure database backups
- [ ] Setup log rotation
- [ ] Enable two-factor authentication enforcement

### Deployment Commands

```bash
# Production build
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Database migration
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan route:clear
```

### Recommended Hosting

- **Server**: Linux (Ubuntu 22.04+)
- **PHP**: 8.2+ with extensions (OpenSSL, PDO, mbstring, ctype, json)
- **Web Server**: Nginx or Apache with mod_rewrite
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Process Manager**: Supervisor or systemd

---

## Security Considerations

### Best Practices Implemented

- Input Validation - All user inputs validated and sanitized
- CSRF Protection - Laravel CSRF token validation
- Password Hashing - bcrypt with configurable rounds
- Session Security - Secure session management
- Two-Factor Authentication - TOTP-based 2FA
- Rate Limiting - Login attempt throttling
- Authorization - Role-based access control
- SQL Injection Prevention - Eloquent ORM parameterized queries
- XSS Protection - Blade template escaping
- Geolocation Privacy - Optional geolocation recording

### Security Recommendations

1. **Always use HTTPS** in production
2. **Keep dependencies updated** - Run `composer update` regularly
3. **Use strong passwords** - Enforce password policies
4. **Enable 2FA** - Require 2FA for admin accounts
5. **Regular backups** - Backup database and files daily
6. **Monitor logs** - Review error and access logs
7. **Update PHP** - Keep PHP and extensions current

---

## Troubleshooting

### QR Code Issues

**Q: QR Code not displaying?**

- Verify `APP_URL` in `.env` is correct
- Check that signed URLs are working
- Ensure cache is cleared: `php artisan cache:clear`

**Q: Token expired before login?**

- Tokens expire after 5 minutes
- User should re-scan QR code
- QR code auto-refreshes every 30 minutes

### Database Issues

**Q: Migration failed?**

- Check database connection in `.env`
- Verify database user has CREATE/ALTER privileges
- Run `php artisan migrate:reset` to clear migrations

**Q: Seeders not working?**

- Ensure database exists
- Run `php artisan migrate` before seeding
- Check for foreign key constraint errors

### Authentication Issues

**Q: Employee can't login?**

- Verify employee account exists
- Check if account is banned
- Ensure password is correct
- Verify QR token is valid (not expired)

---

## Performance

### Optimization Tips

1. **Enable Query Caching**

   ```php
   'cache' => env('DB_QUERY_CACHE', true),
   ```

2. **Use Database Indexing**
   - Attendances table: index on `(user_id, date)`
   - Users table: index on email

3. **Implement Pagination**
   - All list views paginate results
   - Default: 15 items per page

4. **Asset Compression**
   - Run `npm run build` for production
   - Minified CSS & JavaScript

5. **Database Cleanup**
   - Archive old attendance records
   - Delete expired login tokens
   - Purge logs regularly

---

## Contributing

We welcome contributions! Please follow these guidelines:

### Getting Started

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow PSR-12 PHP standards
- Use Laravel conventions
- Write tests for new features
- Update documentation
- Keep commits atomic and descriptive

### Commit Message Format

```
[FEATURE|FIX|DOCS|STYLE|REFACTOR] Short description

Longer explanation if needed.

Fixes #issue_number
```

---

## Roadmap

### Planned Features

- [ ] **Mobile App** - Native iOS/Android attendance app
- [ ] **API Integration** - REST API for third-party systems
- [ ] **Advanced Analytics** - ML-based absence predictions
- [ ] **Multi-Location** - Support multiple office locations
- [ ] **Payroll Integration** - Connect with payroll systems
- [ ] **Email Reports** - Scheduled automated reports
- [ ] **Calendar View** - Interactive attendance calendar
- [ ] **Holiday Management** - Configure company holidays
- [ ] **Shift Scheduling** - Assign employee shifts
- [ ] **Mobile Responsive** - Full mobile optimization

---

## License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## Support

### Getting Help

- Documentation - Check `/docs` directory
- Issue Tracker - Report bugs on GitHub Issues
- Discussions - Ask questions in GitHub Discussions
- Email - Contact <support@example.com>

### Community

- Star the repository - Show your support
- Share feedback - Help us improve
- Contribute - Join the community

---

## Acknowledgments

Built with:

- [Laravel](https://laravel.com) - The PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack reactive components
- [Flux UI](https://fluxui.dev) - Modern component library
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework

---

<div align="center">

**Made with dedication by the Arena Team**

[⬆ Back to top](#arena-système-pointage)

</div>
