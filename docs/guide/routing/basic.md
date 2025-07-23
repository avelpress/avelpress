# Basic Routing

AvelPress provides a powerful routing system that integrates seamlessly with WordPress REST API. It allows you to define clean, RESTful routes for your plugin or theme.

## Introduction

The AvelPress router provides a Laravel-style routing interface that automatically integrates with WordPress REST API endpoints. Routes are typically defined in `routes/api.php` file.

## Basic Route Registration

### Simple Routes

```php
use AvelPress\Facades\Route;

// GET route
Route::get('users', function($request) {
    return ['message' => 'Hello from GET route'];
});

// POST route
Route::post('users', function($request) {
    return ['message' => 'User created'];
});

// PUT route
Route::put('users/{id}', function($request) {
    $id = $request->get_param('id');
    return ['message' => "User {$id} updated"];
});

// DELETE route
Route::delete('users/{id}', function($request) {
    $id = $request->get_param('id');
    return ['message' => "User {$id} deleted"];
});
```

### Available HTTP Methods

```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::delete($uri, $callback);
```

## Route Parameters

### Basic Parameters

You can capture URI segments as parameters:

```php
// Single parameter
Route::get('users/{id}', function($request) {
    $id = $request->get_param('id');
    return ['user_id' => $id];
});

// Multiple parameters
Route::get('users/{userId}/posts/{postId}', function($request) {
    $userId = $request->get_param('userId');
    $postId = $request->get_param('postId');

    return [
        'user_id' => $userId,
        'post_id' => $postId
    ];
});
```

### Optional Parameters

```php
// Optional parameter with default value
Route::get('users/{id?}', function($request) {
    $id = $request->get_param('id') ?: 'all';
    return ['user_id' => $id];
});
```

### Parameter Constraints

You can add regular expression constraints to route parameters:

```php
// Only accept numeric IDs
Route::get('users/{id}', function($request) {
    $id = $request->get_param('id');
    return ['user_id' => $id];
})->where('id', '[0-9]+');

// Multiple constraints
Route::get('users/{id}/posts/{slug}', function($request) {
    // Handle request
})->where(['id' => '[0-9]+', 'slug' => '[a-z-]+']);
```

## Route Callbacks

### Closure Callbacks

Simple closures for quick routes:

```php
Route::get('hello', function($request) {
    return ['message' => 'Hello World!'];
});
```

### Controller Callbacks

Reference controller methods (more organized):

```php
use App\Controllers\UserController;

Route::get('users', [UserController::class, 'index']);
Route::post('users', [UserController::class, 'store']);
Route::get('users/{id}', [UserController::class, 'show']);
Route::put('users/{id}', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'destroy']);
```

## Request Handling

### Accessing Request Data

```php
Route::post('users', function($request) {
    // Get all parameters
    $params = $request->get_params();

    // Get specific parameter
    $name = $request->get_param('name');

    // Get parameter with default
    $email = $request->get_param('email') ?: 'no-email@example.com';

    // Get JSON body (for POST/PUT requests)
    $body = $request->get_json_params();

    // Get file uploads
    $files = $request->get_file_params();

    return ['received' => $params];
});
```

### Request Headers

```php
Route::get('protected', function($request) {
    $authHeader = $request->get_header('authorization');
    $contentType = $request->get_header('content-type');

    if (!$authHeader) {
        return new \WP_Error('unauthorized', 'Authorization required', ['status' => 401]);
    }

    return ['message' => 'Access granted'];
});
```

## Response Handling

### JSON Responses

```php
Route::get('users', function($request) {
    // Simple array (automatically converted to JSON)
    return [
        'users' => [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ]
    ];
});
```

### Custom Status Codes

```php
Route::post('users', function($request) {
    // Return with custom status code
    $response = ['message' => 'User created'];
    return new \WP_REST_Response($response, 201);
});
```

### Error Responses

```php
Route::get('users/{id}', function($request) {
    $id = $request->get_param('id');

    if (!is_numeric($id)) {
        return new \WP_Error(
            'invalid_id',
            'User ID must be numeric',
            ['status' => 400]
        );
    }

    // Find user logic here
    return ['user' => ['id' => $id]];
});
```

## Route Prefixes

### Using Route Groups

```php
// Group routes with common prefix
Route::prefix('api/v1')->group(function() {
    Route::get('users', [UserController::class, 'index']);
    Route::get('posts', [PostController::class, 'index']);
});

// This creates routes:
// /wp-json/your-plugin/api/v1/users
// /wp-json/your-plugin/api/v1/posts
```

### Nested Prefixes

```php
Route::prefix('api')->group(function() {
    Route::prefix('v1')->group(function() {
        Route::get('users', [UserController::class, 'index']);
        Route::get('posts', [PostController::class, 'index']);
    });

    Route::prefix('v2')->group(function() {
        Route::get('users', [UserV2Controller::class, 'index']);
    });
});
```

## Middleware (Guards)

### Route Guards

```php
// Protect routes with authentication
Route::guard('auth')->group(function() {
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
});

// Multiple guards
Route::guard(['auth', 'admin'])->group(function() {
    Route::get('admin/users', [AdminController::class, 'users']);
});
```

### Custom Guards

Create custom middleware in your service provider:

```php
// In your service provider
public function boot()
{
    Route::guard('custom', function($request) {
        // Your custom authentication logic
        if (!$this->isValidRequest($request)) {
            return new \WP_Error('unauthorized', 'Access denied', ['status' => 401]);
        }

        return true; // Allow request to continue
    });
}
```

## Route Model Binding

### Automatic Model Binding

```php
use App\Models\User;

// Automatically inject model instance
Route::get('users/{user}', function(User $user) {
    return new JsonResource($user);
});

// The {user} parameter will automatically resolve to a User model
// based on the ID in the URL
```

### Custom Resolution

```php
// Custom resolution logic
Route::get('users/{user:slug}', function(User $user) {
    return new JsonResource($user);
});

// This will find user by 'slug' field instead of 'id'
```

## Route Caching

For better performance in production, you can cache routes:

```php
// In your service provider
public function boot()
{
    if ($this->app->environment('production')) {
        Route::cache(true);
    }
}
```

## WordPress Integration

### Accessing WordPress Functions

```php
Route::get('current-user', function($request) {
    $current_user = wp_get_current_user();

    if ($current_user->ID === 0) {
        return new \WP_Error('not_logged_in', 'User not logged in', ['status' => 401]);
    }

    return [
        'id' => $current_user->ID,
        'name' => $current_user->display_name,
        'email' => $current_user->user_email
    ];
});
```

### Permission Checks

```php
Route::get('admin/settings', function($request) {
    if (!current_user_can('manage_options')) {
        return new \WP_Error('insufficient_permissions', 'Access denied', ['status' => 403]);
    }

    // Return admin settings
    return get_option('my_plugin_settings', []);
});
```

## Complete Example

Here's a complete example showing various routing features:

```php
<?php

use App\Controllers\UserController;
use App\Controllers\PostController;
use AvelPress\Facades\Route;

// API v1 routes
Route::prefix('api/v1')->group(function() {

    // Public routes
    Route::get('health', function($request) {
        return ['status' => 'ok', 'timestamp' => time()];
    });

    // User routes
    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');

        // Protected routes
        Route::guard('auth')->group(function() {
            Route::post('/', [UserController::class, 'store']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
    });

    // Post routes with nested resources
    Route::prefix('posts')->group(function() {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::get('/{id}/comments', [PostController::class, 'comments']);
    });
});
```

This routing system provides all the flexibility you need to build modern REST APIs while staying integrated with WordPress core functionality.
