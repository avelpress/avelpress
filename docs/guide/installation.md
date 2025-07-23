# Installation

AvelPress can be easily integrated into any WordPress plugin or theme. Follow this guide to get started.

## Requirements

Before installing AvelPress, make sure you have:

- **PHP 7.4+**
- **WordPress 5.0+**
- **Composer** (required)

## Installation Methods

### Method 1: AvelPress CLI (Recommended)

The easiest way to create a new AvelPress project is using the official CLI tool.

#### Install AvelPress CLI globally

```bash
composer global require avelpress/avelpress-cli
```

#### Create a new plugin

```bash
avel new <vendor>/<name>
```

Example:

```bash
avel new acme/my-awesome-plugin
```

This will create a new directory `acme-my-awesome-plugin` with all the necessary files and structure.

> **Note:** Theme support (`--type=theme`) is currently in development. Please use the plugin type for now.

#### Install dependencies

After creating your project, navigate to the directory and install dependencies:

```bash
cd acme-my-awesome-plugin
composer install
```

### Method 2: Manual Composer Installation

If you prefer to set up manually:

```bash
composer require avelpress/avelpress
```

## Project Structure

When you create a new project with `avel new`, it will generate the following structure:

```
acme-my-awesome-plugin/
├── acme-my-awesome-plugin.php    # Main plugin file
├── composer.json
├── assets/
├── src/
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Http/
│   │   ├── Models/
│   │   ├── Modules/
│   │   ├── Providers/
│   │   └── Services/
│   ├── bootstrap/
│   │   └── providers.php
│   ├── config/
│   │   └── app.php
│   ├── database/
│   │   └── migrations/
│   ├── resources/
│   │   └── views/
│   └── routes/
│       └── api.php
└── vendor/                      # Composer dependencies (after install)
```

## Usage

### Generated Plugin File

The CLI automatically generates a main plugin file with proper AvelPress initialization:

```php
<?php
/**
 * Plugin Name: Acme My-awesome-plugin
 * Description: A new AvelPress plugin.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Your Name
 * Text Domain: acme-my-awesome-plugin
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

use AvelPress\AvelPress;

defined( 'ABSPATH' ) || exit;

define( 'ACME_MY_AWESOME_PLUGIN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require ACME_MY_AWESOME_PLUGIN_PLUGIN_PATH . 'vendor/autoload.php';

AvelPress::init( 'acme-my-awesome-plugin', [
	'base_path' => ACME_MY_AWESOME_PLUGIN_PLUGIN_PATH . 'src',
] );
```

### Generated Composer.json

```json
{
  "name": "acme/my-awesome-plugin",
  "description": "A new AvelPress plugin: acme-my-awesome-plugin",
  "version": "1.0.0",
  "type": "wordpress-plugin",
  "license": "GPL-2.0+",
  "authors": [
    {
      "name": "Your Name",
      "email": "your@email.com"
    }
  ],
  "require": {
    "avelpress/avelpress": "^1.0"
  },
  "autoload": {
    "psr-4": {
      "Acme\\MyAwesomePlugin\\": "src/"
    }
  }
}
```

## Configuration

### Basic Configuration

The CLI automatically generates a configuration file at `src/config/app.php`:

```php
<?php

defined( 'ABSPATH' ) || exit;

return [
	'name' => 'Acme-my-awesome-plugin',
	'version' => '1.0.0',
	'debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
];
```

### Service Providers

The CLI automatically generates a providers file at `src/bootstrap/providers.php`:

```php
<?php

defined( 'ABSPATH' ) || exit;

return [
	Acme\MyAwesomePlugin\App\Providers\AppServiceProvider::class,
];
```

It also creates an AppServiceProvider at `src/app/Providers/AppServiceProvider.php`:

```php
<?php

namespace Acme\MyAwesomePlugin\App\Providers;

use AvelPress\Support\ServiceProvider;

defined( 'ABSPATH' ) || exit;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Register any application services.
	 */
	public function register(): void {
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void {
		//
	}
}
```

### API Routes

The CLI also creates a routes file at `src/routes/api.php`:

```php
<?php

use AvelPress\Facades\Route;

defined('ABSPATH') || exit;

//Route::prefix('acme-plugin-example/v1')->guards(['edit_posts'])->group(function () {
//	Route::get('/route-example', [MyController::class, 'my-function']);
//});
```

## Next Steps

Now that AvelPress is installed, you can:

1. [Get Started](/guide/getting-started) with your first AvelPress application
2. Learn about [Application Structure](/guide/application-structure)
3. Create your first [Service Provider](/guide/service-providers)

## Troubleshooting

### Common Issues

**CLI command not found**

- Make sure Composer's global bin directory is in your PATH
- Verify the CLI was installed correctly: `composer global show avelpress/avelpress-cli`

**Project creation fails**

- Ensure you have write permissions in the current directory
- Check if the target directory already exists

**Composer install errors**

- Make sure you're in the correct project directory
- Verify your PHP version meets the requirements
- Check your internet connection for package downloads

**Plugin activation errors**

- Ensure PHP version is 7.4 or higher
- Check WordPress version compatibility
- Verify all Composer dependencies were installed correctly
