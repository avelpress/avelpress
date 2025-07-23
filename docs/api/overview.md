# API Reference Overview

This section provides detailed documentation for all AvelPress framework components, classes, and methods. Use this reference to understand the available functionality and how to implement advanced features.

## Core Components

### Application Layer

- [**Application**](/api/application) - The main application container that manages services and bootstrapping
- [**Service Providers**](/api/service-providers) - Classes that register and bootstrap services
- [**Facades**](/api/facades) - Static interfaces to services in the container

### Routing System

- [**Router**](/api/router) - The main routing class that handles HTTP requests
- [**Controller**](/api/controller) - Base controller class for handling requests
- [**Route Groups**](/api/route-groups) - Grouping routes with common attributes

### Database Layer

- [**Model**](/api/model) - Eloquent-style base model for database interactions
- [**Query Builder**](/api/query-builder) - Fluent query building interface
- [**Schema Builder**](/api/schema) - Database schema definition and migration tools
- [**Migration**](/api/migration) - Database migration system

### HTTP Layer

- [**JSON Resources**](/api/json-resources) - Transform models into JSON responses
- [**Resource Collections**](/api/resource-collections) - Handle collections of resources
- [**Validation**](/api/validation) - Request validation utilities

### Support Classes

- [**Collection**](/api/collection) - Enhanced array manipulation
- [**Paginator**](/api/paginator) - Pagination utilities
- [**Formatter**](/api/formatter) - Data formatting helpers

## Class Reference

### Foundation Classes

| Class                               | Description                                   |
| ----------------------------------- | --------------------------------------------- |
| `AvelPress\AvelPress`               | Main entry point for framework initialization |
| `AvelPress\Foundation\Application`  | Application container and service manager     |
| `AvelPress\Support\ServiceProvider` | Base class for service providers              |

### Routing Classes

| Class                          | Description                    |
| ------------------------------ | ------------------------------ |
| `AvelPress\Routing\Router`     | Main routing engine            |
| `AvelPress\Routing\Controller` | Base controller class          |
| `AvelPress\Facades\Route`      | Route facade for static access |

### Database Classes

| Class                                     | Description              |
| ----------------------------------------- | ------------------------ |
| `AvelPress\Database\Eloquent\Model`       | Base model class         |
| `AvelPress\Database\QueryBuilder`         | Query building interface |
| `AvelPress\Database\Schema\Schema`        | Schema builder facade    |
| `AvelPress\Database\Migrations\Migration` | Base migration class     |
| `AvelPress\Database\Migrations\Migrator`  | Migration runner         |

### HTTP Classes

| Class                                    | Description                 |
| ---------------------------------------- | --------------------------- |
| `AvelPress\Http\Json\JsonResource`       | Base JSON resource class    |
| `AvelPress\Http\Json\ResourceCollection` | Resource collection wrapper |

### Support Classes

| Class                          | Description               |
| ------------------------------ | ------------------------- |
| `AvelPress\Support\Collection` | Enhanced array collection |
| `AvelPress\Support\Paginator`  | Pagination helper         |
| `AvelPress\Support\Validator`  | Input validation          |
| `AvelPress\Support\Formatter`  | Data formatting utilities |

## Facade Reference

Facades provide a static interface to classes in the service container:

| Facade   | Underlying Class   | Purpose                        |
| -------- | ------------------ | ------------------------------ |
| `Route`  | `Router`           | Define routes and route groups |
| `DB`     | `Database`         | Database query operations      |
| `Config` | `ConfigRepository` | Access configuration values    |
| `Schema` | `Schema`           | Database schema operations     |

## Method Naming Conventions

AvelPress follows Laravel naming conventions:

### Models

- **Relationships**: `camelCase` (e.g., `belongsTo()`, `hasMany()`)
- **Scopes**: `scope` prefix + `PascalCase` (e.g., `scopeActive()`)
- **Accessors**: `get` + `PascalCase` + `Attribute` (e.g., `getFullNameAttribute()`)
- **Mutators**: `set` + `PascalCase` + `Attribute` (e.g., `setEmailAttribute()`)

### Controllers

- **CRUD operations**: `index()`, `show()`, `store()`, `update()`, `destroy()`
- **Custom actions**: `camelCase` (e.g., `generateReport()`)

### Routes

- **HTTP methods**: lowercase (e.g., `get()`, `post()`, `put()`, `delete()`)
- **URI patterns**: kebab-case (e.g., `/api/users/{id}/profile-settings`)

## Common Patterns

### Service Container

```php
// Binding services
$app->singleton('service.name', ServiceClass::class);

// Resolving services
$service = $app->make('service.name');
$service = AvelPress::app('service.name');
```

### Dependency Injection

```php
// Constructor injection
public function __construct(UserService $userService)
{
    $this->userService = $userService;
}

// Method injection
public function show(UserService $userService, $request)
{
    // Method implementation
}
```

### Event Handling

```php
// Model events
static::creating(function ($model) {
    // Handle creating event
});

static::created(function ($model) {
    // Handle created event
});
```

## Error Handling

### Standard Error Responses

```php
// Not found
return new \WP_Error('not_found', 'Resource not found', ['status' => 404]);

// Validation error
return new \WP_Error('validation_failed', 'Invalid input', ['status' => 400]);

// Unauthorized
return new \WP_Error('unauthorized', 'Access denied', ['status' => 401]);

// Server error
return new \WP_Error('server_error', 'Internal error', ['status' => 500]);
```

### Exception Handling

```php
try {
    $result = Model::create($data);
} catch (\Exception $e) {
    error_log('Error: ' . $e->getMessage());
    return new \WP_Error('operation_failed', 'Operation failed');
}
```

## Performance Considerations

### Query Optimization

- Use `select()` to limit columns
- Implement proper indexing in migrations
- Use `with()` for eager loading relationships
- Implement caching for expensive queries

### Memory Management

- Use pagination for large datasets
- Clear unused variables and objects
- Implement proper resource cleanup

### Caching Strategies

- Cache configuration values
- Cache query results
- Use WordPress transient API
- Implement cache invalidation

## WordPress Integration

### Hooks and Filters

```php
// In service providers
add_action('init', [$this, 'initialize']);
add_filter('the_content', [$this, 'modifyContent']);
```

### Capabilities and Permissions

```php
// Check permissions
if (!current_user_can('manage_options')) {
    return new \WP_Error('insufficient_permissions', 'Access denied');
}
```

### WordPress Functions

```php
// Access WordPress globals and functions
global $wpdb;
$current_user = wp_get_current_user();
$upload_dir = wp_upload_dir();
```

## Configuration Management

### Environment Detection

```php
// Check environment
if ($this->app->environment('production')) {
    // Production-specific code
}

if ($this->app->environment(['development', 'testing'])) {
    // Development/testing code
}
```

### Configuration Access

```php
use AvelPress\Facades\Config;

// Get configuration
$value = Config::get('app.name');
$value = Config::get('app.debug', false); // with default

// Set configuration (runtime only)
Config::set('app.custom', 'value');
```

This API reference provides the foundation for understanding how to use AvelPress effectively. Each component documentation includes detailed examples and use cases.
