# AvelPress

> A powerful Laravel-inspired framework for WordPress plugin and theme development

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org)
[![Packagist](https://img.shields.io/packagist/v/avelpress/avelpress.svg)](https://packagist.org/packages/avelpress/avelpress)
[![Packagist Downloads](https://img.shields.io/packagist/dt/avelpress/avelpress.svg)](https://packagist.org/packages/avelpress/avelpress)

[📦 View on Packagist](https://packagist.org/packages/avelpress/avelpress)

[📚 Official Documentation](https://avelpress.com)

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

### Create Your First Plugin

```bash
# Create a new plugin
avel new acme/my-awesome-plugin

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

## 📖 Documentation

### Basic Usage

#### 1. Models

Create Eloquent-style models for your data:

```php
<?php

namespace Acme\MyPlugin\App\Models;

use AvelPress\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content', 'status'];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

#### 2. Controllers

Handle HTTP requests with clean controllers:

```php
<?php

namespace Acme\MyPlugin\App\Controllers;

use Acme\MyPlugin\App\Models\Post;
use AvelPress\Routing\Controller;
use AvelPress\Http\Json\JsonResource;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->get();

        return JsonResource::collection($posts);
    }

    public function store($request)
    {
        $post = Post::create([
            'title' => $request->get_param('title'),
            'content' => $request->get_param('content'),
            'status' => 'published'
        ]);

        return new JsonResource($post);
    }
}
```

#### 3. Routes

Define clean API routes:

```php
<?php

use Acme\MyPlugin\App\Controllers\PostController;
use AvelPress\Facades\Route;

Route::prefix('my-plugin/v1')->guards(['edit_posts'])->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});
```

#### 4. Migrations

Version control your database schema:

```bash
# Create a migration
avel make:migration create_posts_table
```

```php
<?php

use AvelPress\Database\Migrations\Migration;
use AvelPress\Database\Schema\Blueprint;
use AvelPress\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::drop('posts');
    }
};
```

#### 5. Service Providers

Organize your application services:

```php
<?php

namespace Acme\MyPlugin\App\Providers;

use AvelPress\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services
        $this->app->bind('my-service', MyService::class);
    }

    public function boot(): void
    {
        // Bootstrap services
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
```

## 🛠️ CLI Commands

AvelPress includes a powerful CLI for rapid development:

```bash
# Create new projects
avel new vendor/plugin-name
avel new vendor/theme-name --type=theme

# Generate migrations
avel make:migration create_users_table
avel make:migration add_email_to_users_table --app-id=my-plugin

# Generate models
avel make:model User
avel make:model Post --migration

# Generate controllers
avel make:controller UserController
avel make:controller PostController --resource
```

## 🔧 Advanced Features

### Database Relationships

```php
// One-to-Many
public function posts()
{
    return $this->hasMany(Post::class);
}

// Many-to-Many
public function tags()
{
    return $this->belongsToMany(Tag::class);
}

// Belongs To
public function author()
{
    return $this->belongsTo(User::class);
}
```

### Query Builder

```php
// Fluent query building
$posts = Post::where('status', 'published')
    ->where('created_at', '>', '2024-01-01')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Advanced queries
$popularPosts = Post::withCount('comments')
    ->having('comments_count', '>', 5)
    ->get();
```

### Validation

```php
use AvelPress\Support\Validator;

$validator = new Validator($request->get_params(), [
    'title' => 'required|string|max:255',
    'email' => 'required|email',
    'age' => 'required|integer|min:18'
]);

if ($validator->fails()) {
    return new WP_Error('validation_failed', 'Validation failed', [
        'status' => 422,
        'errors' => $validator->errors()
    ]);
}
```

## 📚 Documentation

- [Official Documentation](https://avelpress.com)
- [Getting Started](../doc/avelpress-documentation/guide/getting-started.md)
- [Introduction](../doc/avelpress-documentation/guide/introduction.md)
- [Installation](../doc/avelpress-documentation/guide/installation.md)
- [WordPress Integration](../doc/avelpress-documentation/guide/wordpress-integration.md)
- [Application Structure](../doc/avelpress-documentation/guide/core/application-structure.md)
- [CLI Commands](../doc/avelpress-documentation/guide/core/cli.md)
- [Service Providers](../doc/avelpress-documentation/guide/core/service-providers.md)
- [Collections](../doc/avelpress-documentation/guide/core/collections.md)
- [Database Getting Started](../doc/avelpress-documentation/guide/database/getting-started.md)
- [Eloquent ORM](../doc/avelpress-documentation/guide/database/eloquent.md)
- [Migrations](../doc/avelpress-documentation/guide/database/migrations.md)
- [Models](../doc/avelpress-documentation/guide/models/model.md)
- [Routing](../doc/avelpress-documentation/guide/routing/overview.md)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/avelpress/avelpress.git
cd avelpress

# Install dependencies
composer install

# Run tests
composer test
```

<p align="center">
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License: MIT"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-7.4%2B-blue.svg" alt="PHP Version"></a>
  <a href="https://wordpress.org"><img src="https://img.shields.io/badge/WordPress-5.0%2B-blue.svg" alt="WordPress"></a>
  <a href="https://packagist.org/packages/avelpress/avelpress"><img src="https://img.shields.io/badge/Packagist-v1.0.0-blue.svg" alt="Packagist"></a>
  <a href="https://packagist.org/packages/avelpress/avelpress"><img src="https://img.shields.io/badge/Downloads-1000%2B-brightgreen.svg" alt="Packagist Downloads"></a>
</p>
## 📄 License

AvelPress is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Inspired by [Laravel](https://laravel.com) framework
- Built for the [WordPress](https://wordpress.org) ecosystem
- Thanks to all [contributors](https://github.com/avelpress/avelpress/contributors)

## 🔗 Links

- [Documentation](https://avelpress.com)
- [CLI Tool](https://github.com/avelpress/avelpress-cli)
- [Examples](https://github.com/avelpress/avelpress-examples)
- [Community](https://discord.gg/avelpress)

---

<p align="center">
  <strong>Made with ❤️ for the WordPress community</strong>
</p>
