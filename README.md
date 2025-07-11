# Laravel + Inertia React RBAC Starter

A modern full-stack starter kit built with Laravel, Inertia.js, React, and TypeScript. Features role-based access control (RBAC), multi-tenancy, JWT authentication, and file upload capabilities with MinIO.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)
![React](https://img.shields.io/badge/React-18.x-61DAFB?style=flat-square&logo=react)
![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=flat-square&logo=typescript)
![Inertia.js](https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=flat-square&logo=inertia)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css)

## ✨ Features

### 🔐 Authentication & Authorization
- **JWT Authentication** with refresh token support
- **Role-Based Access Control (RBAC)** using Laravel Bouncer
- **Multi-tenant architecture** with tenant isolation
- **Password reset** and email verification
- **Session management** with secure logout

### 👥 User Management
- **User CRUD operations** with role-based permissions
- **Profile management** with avatar upload
- **Account deactivation** (soft delete)
- **Password change** functionality
- **User roles and permissions** management

### 🏢 Multi-Tenancy
- **Tenant isolation** for data security
- **Role-based tenant access** (Developer, Admin, Staff)
- **Tenant-specific user management**
- **Scalable architecture** for SaaS applications

### 📁 File Management
- **Avatar upload/delete** with image validation
- **MinIO integration** for scalable file storage
- **Automatic file cleanup** when replacing files
- **Image optimization** and validation

### 🎨 Modern UI/UX
- **ShadCN/UI components** with Tailwind CSS
- **Dark/Light mode** support
- **Responsive design** for all devices
- **Professional dashboard** layout
- **Form validation** with real-time feedback

## 🛠️ Tech Stack

### Backend
- **Laravel 11.x** - PHP framework
- **Laravel Bouncer** - Role and permission management
- **JWT Auth** - Stateless authentication
- **Laravel Sanctum** - API authentication
- **MinIO** - Object storage
- **MySQL/PostgreSQL** - Database

### Frontend
- **React 18.x** - UI library
- **TypeScript** - Type safety
- **Inertia.js** - Server-side routing
- **Tailwind CSS** - Utility-first CSS
- **ShadCN/UI** - UI component library
- **Vite** - Fast build tool

### Development Tools
- **Pest PHP** - Testing framework
- **ESLint** - JavaScript linting
- **Prettier** - Code formatting
- **Composer** - PHP dependency management
- **NPM** - Node.js package manager

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Node.js 18+
- Composer
- MySQL/PostgreSQL
- MinIO (for file storage)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd rbac
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install JavaScript dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure environment variables**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rbac
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60
JWT_REFRESH_TTL=20160

# MinIO Configuration
MINIO_ENDPOINT=http://localhost:9000
MINIO_KEY=your-minio-key
MINIO_SECRET=your-minio-secret
MINIO_REGION=us-east-1
MINIO_BUCKET=your-bucket-name
```

6. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

7. **Build assets**
```bash
npm run build
# or for development
npm run dev
```

8. **Start the server**
```bash
php artisan serve
```

## 📊 Database Schema

### Core Tables
- `users` - User accounts with soft deletes
- `tenants` - Multi-tenant organization data
- `abilities` - Permission definitions (Bouncer)
- `roles` - Role definitions (Bouncer)
- `assigned_roles` - User-role assignments (Bouncer)

### Key Relationships
- Users belong to Tenants
- Users have Roles through Bouncer
- Roles have Abilities (Permissions)
- Soft delete support for data recovery

## 🔑 Default Roles & Permissions

### Roles
- **Developer** - Full system access
- **Admin** - Tenant administration
- **Staff** - Limited tenant access

### Permissions
- **Tenant Management**: `read-all-tenants`, `create-tenants`, `update-all-tenants`, `delete-all-tenants`
- **User Management**: `read-users`, `create-users`, `update-users`, `delete-users`
- **Role Management**: `read-roles`, `create-roles`, `update-roles`, `delete-roles`
- **Permission Management**: `read-permissions`, `create-permissions`, `update-permissions`, `delete-permissions`

## 🚦 API Endpoints

### Authentication
```http
POST   /api/auth/register      # User registration
POST   /api/auth/login         # User login
POST   /api/auth/logout        # User logout
POST   /api/auth/refresh       # Refresh JWT token
GET    /api/auth/me            # Get authenticated user
GET    /api/auth/permissions   # Get user permissions
```

### Profile Management
```http
GET    /api/profile            # Get user profile
PATCH  /api/profile/biodata    # Update profile information
PATCH  /api/profile/password   # Change password
POST   /api/profile/avatar     # Upload avatar
DELETE /api/profile/avatar     # Delete avatar
DELETE /api/profile/deactivate # Deactivate account
```

### User Management (Role-based access)
```http
GET    /api/users              # List users
POST   /api/users              # Create user
GET    /api/users/{id}         # Get user details
PUT    /api/users/{id}         # Update user
DELETE /api/users/{id}         # Delete user
```

### Tenant Management
```http
GET    /api/tenants            # List all tenants (Developer only)
POST   /api/tenants            # Create tenant (Developer only)
GET    /api/tenants/{id}       # Get tenant details
PUT    /api/tenants/{id}       # Update tenant
DELETE /api/tenants/{id}       # Delete tenant
GET    /api/my-tenant          # Get own tenant info
PUT    /api/my-tenant          # Update own tenant
```

## 🌐 Web Routes

### Authentication
- `/login` - Login page
- `/register` - Registration page
- `/forgot-password` - Password reset request
- `/reset-password` - Password reset form

### Dashboard
- `/dashboard` - Main dashboard
- `/users` - User management
- `/roles` - Role management
- `/permissions` - Permission management

### Settings
- `/settings/profile` - Profile settings
- `/settings/password` - Password change
- `/settings/appearance` - UI preferences

## 🧪 Testing

### Run all tests
```bash
php artisan test
# or with Pest
./vendor/bin/pest
```

### Run specific test suites
```bash
# Authentication tests
php artisan test tests/Feature/Auth/

# Settings tests
php artisan test tests/Feature/Settings/

# User management tests
php artisan test tests/Feature/UserTest.php
```

### Test Coverage
- ✅ User authentication flow
- ✅ Profile management
- ✅ Password security
- ✅ Soft delete functionality
- ✅ File upload validation
- ✅ Role-based access control

## 🔧 Configuration

### MinIO Setup
1. Install MinIO server
2. Create bucket for file storage
3. Configure CORS for web access
4. Update environment variables

### JWT Configuration
```bash
# Generate JWT secret
php artisan jwt:secret

# Publish JWT config (optional)
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### Bouncer Setup (Already configured)
```bash
# Publish Bouncer migrations (already done)
php artisan vendor:publish --tag="bouncer.migrations"
```

## 🎨 UI Customization

### Theme Configuration
```typescript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Custom color palette
      }
    }
  }
}
```

### Component Library
Uses ShadCN/UI components:
- Button, Input, Label
- Avatar, Card, Dialog
- Navigation, Sidebar
- Form validation components

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Configure secure database
- [ ] Set up MinIO cluster
- [ ] Configure Redis for sessions
- [ ] Set up SSL certificates
- [ ] Configure proper CORS
- [ ] Set up monitoring and logging

### Docker Deployment (Optional)
```bash
# Build and run with Docker
docker-compose up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use TypeScript for frontend code
- Follow Laravel best practices

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 📞 Support

For support and questions:
- 📧 Email: support@example.com
- 💬 Discord: [Join our community](#)
- 📖 Documentation: [Full docs](#)
- 🐛 Issues: [GitHub Issues](#)

## 🙏 Acknowledgments

- Laravel team for the amazing framework
- Inertia.js for seamless SPA experience
- React community for the UI library
- ShadCN for beautiful UI components
- All contributors and supporters

---

**Happy coding! 🚀** 