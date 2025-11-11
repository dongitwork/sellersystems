# User Management System

Modern user management system với PHP 8.2+, MySQL/MariaDB 10.3+, Slim Framework, Medoo và Bootstrap 5.

## 🚀 Cài Đặt Nhanh

```bash
# 1. Chạy installer
php install_user_system.php

# 2. Cài đặt dependencies
cd user-management-system
composer install

# 3. Import database
mysql -u root -p < database.sql

# 4. Cấu hình .env
cp .env.example .env
# Chỉnh sửa thông tin database trong .env

# 5. Chạy ứng dụng
php -S localhost:8000 -t public
```

## 📁 Cấu Trúc

```
user-management-system/
├── app/                    # Core application
│   ├── Core/              # Base classes
│   ├── Config/            # Configurations
│   ├── Middleware/        # Middlewares
│   └── Helpers/           # Helper functions
├── modules/               # Modules (Auth, Dashboard, User)
│   └── [Module]/
│       ├── Controllers/
│       ├── Models/
│       ├── Views/
│       └── Routes/
├── public/                # Public directory
├── resources/             # Shared resources
│   └── views/
│       ├── layouts/       # Layout templates
│       └── components/    # Reusable components
└── vendor/                # Composer dependencies
```

## ✨ Tính Năng

### 🔐 Authentication & Authorization
- Login/Register với validation
- Password hashing (bcrypt)
- Remember me token
- Role-based access control

### 👥 User Management
- CRUD operations
- Multiple roles per user
- Status management (active/inactive/banned)
- API tokens

### 🎨 Modern UI
- Bootstrap 5
- Responsive design
- Gradient backgrounds
- Bootstrap Icons

### 🏗️ Architecture
- PSR-4 Autoloading
- Modular MVC structure
- Dependency Injection
- Middleware system

## 🔑 Default Login

```
Username: admin
Password: admin123
```

## 🛠️ Requirements

- PHP 8.2+
- MySQL/MariaDB 10.3+
- Composer
- Apache/Nginx (optional)

## 📝 License

MIT License