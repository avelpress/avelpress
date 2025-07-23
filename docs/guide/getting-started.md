# Getting Started

This guide will walk you through creating your first AvelPress application. We'll build a simple plugin that demonstrates the core concepts of the framework.

## Creating Your First Plugin

Let's create a simple "Task Manager" plugin to demonstrate AvelPress capabilities using the AvelPress CLI.

### 1. Create the Plugin with CLI

First, use the AvelPress CLI to create your new plugin:

```bash
avel new acme/task-manager
cd acme-task-manager
composer install
```

This will generate the following structure:

```
acme-task-manager/
├── acme-task-manager.php           # Main plugin file
├── composer.json
├── assets/
├── src/
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Http/
│   │   ├── Models/
│   │   ├── Modules/
│   │   ├── Providers/
│   │   │   └── AppServiceProvider.php
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
└── vendor/                        # Composer dependencies
```

### 2. Understanding the Generated Files

The CLI has already created the basic structure. Let's examine the key files:

#### Main Plugin File (`acme-task-manager.php`)

```php
<?php
/**
 * Plugin Name: Acme Task-manager
 * Description: A new AvelPress plugin.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Your Name
 * Text Domain: acme-task-manager
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

use AvelPress\AvelPress;

defined( 'ABSPATH' ) || exit;

define( 'ACME_TASK_MANAGER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require ACME_TASK_MANAGER_PLUGIN_PATH . 'vendor/autoload.php';

AvelPress::init( 'acme-task-manager', [
	'base_path' => ACME_TASK_MANAGER_PLUGIN_PATH . 'src',
] );
```

#### Configuration (`src/config/app.php`)

```php
<?php

defined( 'ABSPATH' ) || exit;

return [
	'name' => 'Acme-task-manager',
	'version' => '1.0.0',
	'debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
];
```

#### Service Providers (`src/bootstrap/providers.php`)

```php
<?php

defined( 'ABSPATH' ) || exit;

return [
	Acme\TaskManager\App\Providers\AppServiceProvider::class,
];
```

#### AppServiceProvider (`src/app/Providers/AppServiceProvider.php`)

```php
<?php

namespace Acme\TaskManager\App\Providers;

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

### 3. Building the Task Manager

Now let's add the functionality to create a task management system.

#### Database Migration

Use the AvelPress CLI to create a migration:

```bash
avel make:migration create_tasks_table
```

This will create a migration file in `src/database/migrations/` with a timestamp prefix (e.g., `2024_07_22_143052_create_tasks_table.php`).

The generated file will look like this:

```php
<?php

use AvelPress\Database\Migrations\Migration;
use AvelPress\Database\Schema\Blueprint;
use AvelPress\Database\Schema\Schema;

defined( 'ABSPATH' ) || exit;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create( 'tasks', function (Blueprint $table) {
			// Add columns to the table
		} );
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::drop( 'tasks' );
	}
};
```

Now update the `up()` method to define your table structure:

```php
public function up(): void {
	Schema::create( 'tasks', function (Blueprint $table) {
		$table->id();
		$table->string('title');
		$table->text('description')->nullable();
		$table->boolean('completed')->default(false);
		$table->timestamps();
	} );
}
```

> **Tip:** The CLI automatically detects table names from migration names. Use patterns like:
>
> - `create_tasks_table` - Creates a new table
> - `add_column_to_tasks_table` - Modifies existing table
> - Use `--app-id=your-app` to prefix table names automatically

#### Model

Create `src/app/Models/Task.php`:

```php
<?php

namespace Acme\TaskManager\App\Models;

use AvelPress\Database\Eloquent\Model;

defined( 'ABSPATH' ) || exit;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'completed'
    ];

    public $timestamps = true;

    // Scope for completed tasks
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    // Scope for pending tasks
    public function scopePending($query)
    {
        return $query->where('completed', false);
    }
}
```

#### Controller

Create `src/app/Controllers/TaskController.php`:

```php
<?php

namespace Acme\TaskManager\App\Controllers;

use Acme\TaskManager\App\Models\Task;
use AvelPress\Routing\Controller;
use AvelPress\Http\Json\JsonResource;

defined( 'ABSPATH' ) || exit;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();

        return JsonResource::collection($tasks);
    }

    public function store($request)
    {
        $task = Task::create([
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'completed' => false
        ]);

        return new JsonResource($task);
    }

    public function show($request)
    {
        $id = $request->get_param('id');
        $task = Task::find($id);

        if (!$task) {
            return new \WP_Error('task_not_found', 'Task not found', ['status' => 404]);
        }

        return new JsonResource($task);
    }

    public function update($request)
    {
        $id = $request->get_param('id');
        $task = Task::find($id);

        if (!$task) {
            return new \WP_Error('task_not_found', 'Task not found', ['status' => 404]);
        }

        $task->update([
            'title' => $request->get_param('title') ?: $task->title,
            'description' => $request->get_param('description') ?: $task->description,
            'completed' => $request->get_param('completed') ?? $task->completed
        ]);

        return new JsonResource($task);
    }

    public function destroy($request)
    {
        $id = $request->get_param('id');
        $task = Task::find($id);

        if (!$task) {
            return new \WP_Error('task_not_found', 'Task not found', ['status' => 404]);
        }

        $task->delete();

        return ['message' => 'Task deleted successfully'];
    }
}
```

#### Routes

Update `src/routes/api.php`:

```php
<?php

use Acme\TaskManager\App\Controllers\TaskController;
use AvelPress\Facades\Route;

defined('ABSPATH') || exit;

Route::prefix('task-manager/v1')->guards(['edit_posts'])->group(function () {
	Route::get('/tasks', [TaskController::class, 'index']);
	Route::post('/tasks', [TaskController::class, 'store']);
	Route::get('/tasks/{id}', [TaskController::class, 'show']);
	Route::put('/tasks/{id}', [TaskController::class, 'update']);
	Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
```

## Testing Your Plugin

### 1. Activate the Plugin

1. Upload your plugin to `/wp-content/plugins/`
2. Activate it in the WordPress admin

### 2. Test API Endpoints

You can now test your API endpoints:

```bash
# Get all tasks
curl -X GET "https://yoursite.com/wp-json/task-manager/v1/tasks"

# Create a task
curl -X POST "https://yoursite.com/wp-json/task-manager/v1/tasks" \
  -H "Content-Type: application/json" \
  -d '{"title": "My First Task", "description": "This is a test task"}'

# Get a specific task
curl -X GET "https://yoursite.com/wp-json/task-manager/v1/tasks/1"

# Update a task
curl -X PUT "https://yoursite.com/wp-json/task-manager/v1/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{"completed": true}'

# Delete a task
curl -X DELETE "https://yoursite.com/wp-json/task-manager/v1/tasks/1"
```

## What's Next?

Congratulations! You've created your first AvelPress plugin. Here's what you can explore next:

- [Application Structure](/guide/application-structure) - Understand how AvelPress organizes code
- [Service Providers](/guide/service-providers) - Learn about dependency injection and service containers
- [Database Relationships](/guide/database/relationships) - Add relationships between models
- [JSON Resources](/guide/http/json-resources) - Customize API responses
- [Validation](/guide/http/validation) - Add request validation

## Key Concepts Learned

In this tutorial, you learned:

- **Application Initialization** - How to bootstrap AvelPress
- **Service Providers** - How to organize and register services
- **Migrations** - How to manage database schema changes
- **Models** - How to interact with the database using Eloquent-style syntax
- **Controllers** - How to handle HTTP requests
- **Routing** - How to define API endpoints
- **JSON Resources** - How to format API responses
