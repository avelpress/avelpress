# Frequently Asked Questions

## General Questions

### What is AvelPress?

AvelPress is a pure PHP framework designed to streamline WordPress plugin and theme development. It brings Laravel-inspired architecture to WordPress without adding external dependencies or bloat.

### Why should I use AvelPress instead of plain WordPress development?

AvelPress provides:

- **Organized Code Structure**: Laravel-like MVC architecture
- **Modern PHP Practices**: Dependency injection, service containers, facades
- **Database Management**: Eloquent-style models and migrations
- **API Development**: Built-in REST API integration
- **Zero Dependencies**: No external packages required
- **WordPress Compatibility**: Full integration with WordPress core

### Is AvelPress compatible with all WordPress versions?

AvelPress requires WordPress 5.0+ and PHP 7.4+. It's designed to work with current and future WordPress versions while maintaining backward compatibility.

## Installation and Setup

### How do I install AvelPress?

1. Download or clone the AvelPress framework
2. Include it in your plugin or theme directory
3. Initialize it in your main plugin file:

```php
require_once __DIR__ . '/avelpress/src/AvelPress.php';
use AvelPress\AvelPress;

$app = AvelPress::init('my-plugin', [
    'base_path' => __DIR__,
]);
```

### Do I need Composer to use AvelPress?

No, AvelPress doesn't require Composer. It's built to work without external dependencies. However, you can use Composer for autoloading your own classes if desired.

### Can I use AvelPress in a theme?

Yes! AvelPress works in both plugins and themes. For themes, initialize it in your `functions.php` file:

```php
$app = AvelPress::init('my-theme', [
    'base_path' => get_template_directory(),
]);
```

## Database and Models

### How does AvelPress handle WordPress table prefixes?

AvelPress automatically uses WordPress table prefixes. You can also define custom prefixes in your configuration:

```php
// config/app.php
return [
    'db_prefix' => 'myplugin_',
];
```

### Can I use AvelPress models with existing WordPress tables?

Yes! You can create models for WordPress core tables:

```php
class WPPost extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    // ... other configurations
}
```

### How do I run migrations?

Migrations can be run automatically on plugin activation:

```php
register_activation_hook(__FILE__, function() {
    $migrator = AvelPress::app('migrator');
    $migrator->run();
});
```

### What happens to my data if I deactivate the plugin?

By default, data remains in the database when you deactivate a plugin. You can add cleanup code in the deactivation hook if needed:

```php
register_deactivation_hook(__FILE__, function() {
    // Optional: Clean up data
});
```

## Routing and API

### How does AvelPress routing integrate with WordPress REST API?

AvelPress routes automatically become WordPress REST API endpoints. A route like:

```php
Route::get('users', [UserController::class, 'index']);
```

Becomes accessible at: `/wp-json/your-plugin/users`

### Can I use WordPress authentication with AvelPress routes?

Yes! AvelPress routes inherit WordPress authentication. You can also add custom guards:

```php
Route::guard('auth')->group(function() {
    // Protected routes
});
```

### How do I handle CORS for API requests?

You can handle CORS in your service provider:

```php
public function boot()
{
    add_action('rest_api_init', function() {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', [$this, 'corsHeaders']);
    });
}

public function corsHeaders($value)
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    return $value;
}
```

## Performance and Optimization

### Does AvelPress affect WordPress performance?

AvelPress is designed to be lightweight and performant. It only loads when your plugin/theme is active and uses WordPress's existing infrastructure.

### How can I optimize AvelPress applications?

1. **Use pagination** for large datasets
2. **Implement caching** for expensive operations
3. **Eager load relationships** to avoid N+1 queries
4. **Index database columns** properly in migrations
5. **Use WordPress transients** for temporary data

### Can I cache AvelPress responses?

Yes! You can implement caching at multiple levels:

```php
// In your controller
public function index($request)
{
    $cacheKey = 'users_list_' . md5(serialize($request->get_params()));
    $users = wp_cache_get($cacheKey);

    if ($users === false) {
        $users = User::paginate(20);
        wp_cache_set($cacheKey, $users, '', 3600);
    }

    return new ResourceCollection($users);
}
```

## Development Workflow

### How do I debug AvelPress applications?

1. **Enable WordPress debug mode**:

   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Use error logging**:

   ```php
   error_log('Debug info: ' . print_r($data, true));
   ```

3. **Check REST API responses** in browser developer tools

4. **Use WordPress debug bar** plugin for additional insights

### Can I use AvelPress with other WordPress plugins?

Yes! AvelPress is designed to coexist with other plugins. It doesn't modify WordPress core functionality or conflict with other plugins.

### How do I test AvelPress applications?

You can test at multiple levels:

1. **Unit tests** for models and classes
2. **API tests** using tools like Postman or curl
3. **Integration tests** with WordPress testing framework
4. **Manual testing** through WordPress admin and frontend

## Compatibility and Migration

### Can I migrate existing WordPress plugins to AvelPress?

Yes! You can gradually migrate existing plugins:

1. Start by creating AvelPress models for existing data
2. Add new features using AvelPress architecture
3. Gradually refactor existing code to use AvelPress patterns

### What if I want to stop using AvelPress?

Since AvelPress doesn't modify WordPress core or database structure, you can:

1. Extract your business logic
2. Convert models back to direct WordPress functions
3. Remove AvelPress framework files
4. Update your plugin structure

Your data will remain intact.

## Advanced Usage

### Can I create custom service providers?

Yes! Create custom service providers to organize your application:

```php
class CustomServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('custom.service', CustomService::class);
    }

    public function boot()
    {
        // Bootstrap custom functionality
    }
}
```

### How do I handle file uploads with AvelPress?

Use WordPress's built-in file handling functions:

```php
public function uploadImage($request)
{
    $files = $request->get_file_params();

    if (empty($files['image'])) {
        return new \WP_Error('no_file', 'No file uploaded');
    }

    $upload = wp_handle_upload($files['image'], ['test_form' => false]);

    if (isset($upload['error'])) {
        return new \WP_Error('upload_failed', $upload['error']);
    }

    return ['url' => $upload['url']];
}
```

### Can I use AvelPress with multisite?

Yes! AvelPress works with WordPress multisite. Each site can have its own AvelPress applications, and you can create network-wide plugins that use AvelPress.

### How do I schedule background tasks?

Use WordPress cron with AvelPress:

```php
// In your service provider
public function boot()
{
    add_action('my_scheduled_task', [$this, 'processTask']);

    if (!wp_next_scheduled('my_scheduled_task')) {
        wp_schedule_event(time(), 'hourly', 'my_scheduled_task');
    }
}

public function processTask()
{
    // Use AvelPress models and services
    $users = User::where('status', 'pending')->limit(100)->get();
    // Process users...
}
```

## Troubleshooting

### Common Error: "Class not found"

**Cause**: AvelPress files not properly included or autoloader not set up.

**Solution**: Ensure the path to AvelPress is correct:

```php
require_once __DIR__ . '/path/to/avelpress/src/AvelPress.php';
```

### Common Error: "Table doesn't exist"

**Cause**: Migrations haven't been run.

**Solution**: Run migrations:

```php
$migrator = AvelPress::app('migrator');
$migrator->run();
```

### Common Error: "Route not found"

**Cause**: Routes file not loaded or syntax error.

**Solution**: Check your `RouteServiceProvider` and routes file syntax.

### API returns empty response

**Cause**: Usually a PHP error or missing return statement.

**Solution**:

1. Check WordPress debug logs
2. Ensure controllers return data
3. Verify JSON resource formatting

### Database queries not working

**Cause**: Incorrect table names or prefixes.

**Solution**:

1. Check model table configuration
2. Verify database prefixes
3. Use WordPress database inspection tools

## Getting Help

### Where can I find more examples?

- Check the `/examples` directory in documentation
- Look at the API reference for detailed method documentation
- Study the included sample plugins

### How do I report bugs or request features?

- Create issues on the GitHub repository
- Include detailed reproduction steps
- Provide error logs and environment information

### Can I contribute to AvelPress?

Yes! Contributions are welcome:

- Submit bug fixes and improvements
- Add documentation and examples
- Share feedback and use cases

### Is there a community forum?

Check the GitHub repository for discussions and community interaction. You can also reach out through the issue tracker for questions and support.
