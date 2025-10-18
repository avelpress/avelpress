# AvelPress

> A powerful Laravel-inspired framework for WordPress plugin and theme development

<p align="center">
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License: MIT"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-7.4%2B-blue.svg" alt="PHP Version"></a>
  <a href="https://wordpress.org"><img src="https://img.shields.io/badge/WordPress-5.0%2B-blue.svg" alt="WordPress"></a>
  <a href="https://packagist.org/packages/avelpress/avelpress"><img src="https://img.shields.io/packagist/v/avelpress/avelpress.svg" alt="Packagist"></a>
  <a href="https://packagist.org/packages/avelpress/avelpress"><img src="https://img.shields.io/packagist/dt/avelpress/avelpress.svg" alt="Packagist Downloads"></a>
</p>

[📚 Official Documentation](https://avelpress.com)

[📦 View on Packagist](https://packagist.org/packages/avelpress/avelpress)

AvelPress brings the elegance and power of Laravel's architecture to WordPress development. Build robust, maintainable plugins and themes using familiar patterns like Eloquent ORM, Service Providers, Facades, and more.

## ✨ Features

- **🏗️ Laravel-inspired Architecture** - Familiar MVC patterns and structure
- **🗄️ Eloquent-style ORM** - Powerful database interactions with models and relationships
- **🛤️ Elegant Routing** - Clean API routing with middleware support
- **🔧 Service Container** - Dependency injection and service providers
- **📦 CLI Tool** - Generate projects, migrations, and boilerplate code
- **🔄 Database Migrations** - Version control for your database schema
- **🎨 Blade-like Templates** - Clean templating system
- **✅ Validation** - Built-in request validation
- **🎭 Facades** - Static-like interfaces to dynamic objects

## 🚀 Quick Start

### Installation

Install the AvelPress CLI globally:

```bash
composer global require avelpress/avelpress-cli
```

Or Install locally

```bash
composer require avelpress/avelpress-cli --dev
```

### Create Your First Plugin

```bash
# Create a new plugin
avel new acme/my-awesome-plugin

# if installed locally
./vendor/bin/avel new acme/my-awesome-plugin

# Navigate to the project
cd acme-my-awesome-plugin

# Install dependencies
composer install
```

### Project Structure

```
acme-my-awesome-plugin/
├── acme-my-awesome-plugin.php    # Main plugin file
├── composer.json
├── assets/
├── src/
│   ├── app/
│   │   ├── Controllers/          # HTTP Controllers
│   │   ├── Models/              # Eloquent Models
│   │   ├── Providers/           # Service Providers
│   │   └── Services/            # Business Logic
│   ├── bootstrap/
│   │   └── providers.php        # Register providers
│   ├── config/
│   │   └── app.php             # Application config
│   ├── database/
│   │   └── migrations/         # Database migrations
│   ├── resources/
│   │   └── views/             # Template files
│   └── routes/
│       └── api.php            # API routes
└── vendor/                    # Composer dependencies
```

## 🛠️ CLI Commands

AvelPress includes a powerful CLI for rapid development:

```bash
# Create new plugin project
avel new vendor/plugin-name

# Make migration
avel make:migration create_users_table
avel make:migration add_email_to_users_table

# Make model
avel make:model User

# Generate basic controller
avel make:controller UserController
# Generate controller with CRUD methods
avel make:controller PostController --resource

# Build plugin
avel build
# Build plugin and ignore composer requirements
avel build --ignore-platform-reqs
```

## 📚 Documentation

- [Official Documentation](https://avelpress.com)

## 📄 License

AvelPress is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Inspired by [Laravel](https://laravel.com) framework
- Built for the [WordPress](https://wordpress.org) ecosystem
- Thanks to all [contributors](https://github.com/avelpress/avelpress/contributors)

## 🔗 Links

- [Documentation](https://avelpress.com)
- [CLI Tool](https://github.com/avelpress/avelpress-cli)

---

<p align="center">
  <strong>Made with ❤️ for the WordPress community</strong>
</p>
