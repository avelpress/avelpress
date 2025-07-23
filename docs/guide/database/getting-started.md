# Database - Getting Started

AvelPress provides a powerful database layer that abstracts WordPress database operations while maintaining compatibility with WordPress core functions. It includes query builders, migrations, and Eloquent-style models.

## Database Connection

AvelPress automatically uses the WordPress database connection, so no additional configuration is required. It uses the same database credentials and connection that WordPress uses.

### Accessing the Database

```php
use AvelPress\Facades\DB;

// Get the database instance
$db = DB::connection();

// Or use the facade directly
$users = DB::table('users')->get();
```

## Query Builder

The query builder provides a fluent interface for building database queries:

### Basic Queries

```php
use AvelPress\Facades\DB;

// Select all records
$users = DB::table('users')->get();

// Select specific columns
$users = DB::table('users')->select('id', 'name', 'email')->get();

// Select with where clause
$user = DB::table('users')->where('id', 1)->first();

// Multiple where conditions
$users = DB::table('users')
    ->where('status', 'active')
    ->where('age', '>', 18)
    ->get();
```

### Where Clauses

```php
// Basic where
DB::table('users')->where('name', 'John')->get();

// Where with operator
DB::table('users')->where('age', '>', 18)->get();
DB::table('users')->where('age', '>=', 21)->get();

// Where in
DB::table('users')->whereIn('id', [1, 2, 3])->get();

// Where not in
DB::table('users')->whereNotIn('status', ['banned', 'suspended'])->get();

// Where null
DB::table('users')->whereNull('deleted_at')->get();

// Where not null
DB::table('users')->whereNotNull('email')->get();

// Where between
DB::table('products')->whereBetween('price', [10, 100])->get();

// Where like
DB::table('users')->where('name', 'like', '%john%')->get();
```

### Ordering and Limiting

```php
// Order by
$users = DB::table('users')->orderBy('name', 'asc')->get();
$users = DB::table('users')->orderBy('created_at', 'desc')->get();

// Multiple order by
$users = DB::table('users')
    ->orderBy('name', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();

// Limit
$users = DB::table('users')->limit(10)->get();

// Offset and limit
$users = DB::table('users')->offset(20)->limit(10)->get();

// Take (alias for limit)
$users = DB::table('users')->take(5)->get();

// Skip (alias for offset)
$users = DB::table('users')->skip(10)->take(5)->get();
```

### Aggregates

```php
// Count
$count = DB::table('users')->count();
$activeCount = DB::table('users')->where('status', 'active')->count();

// Sum
$totalPrice = DB::table('orders')->sum('total');

// Average
$avgAge = DB::table('users')->avg('age');

// Min and Max
$minPrice = DB::table('products')->min('price');
$maxPrice = DB::table('products')->max('price');
```

### Grouping and Having

```php
// Group by
$stats = DB::table('orders')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

// Having
$stats = DB::table('orders')
    ->select('user_id', DB::raw('SUM(total) as total_spent'))
    ->groupBy('user_id')
    ->having('total_spent', '>', 1000)
    ->get();
```

## Insert, Update, Delete

### Insert Operations

```php
// Insert single record
$id = DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => current_time('mysql')
]);

// Insert and get ID
$id = DB::table('users')->insertGetId([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);

// Insert multiple records
DB::table('users')->insert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
]);
```

### Update Operations

```php
// Update records
DB::table('users')
    ->where('id', 1)
    ->update(['name' => 'Updated Name']);

// Update multiple fields
DB::table('users')
    ->where('status', 'inactive')
    ->update([
        'status' => 'active',
        'updated_at' => current_time('mysql')
    ]);

// Increment/Decrement
DB::table('posts')->where('id', 1)->increment('views');
DB::table('posts')->where('id', 1)->increment('views', 5);
DB::table('users')->where('id', 1)->decrement('credits');
```

### Delete Operations

```php
// Delete records
DB::table('users')->where('id', 1)->delete();

// Delete with multiple conditions
DB::table('users')
    ->where('status', 'banned')
    ->where('last_login', '<', '2023-01-01')
    ->delete();

// Truncate table
DB::table('temp_data')->truncate();
```

## Joins

```php
// Inner join
$users = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->select('users.name', 'profiles.bio')
    ->get();

// Left join
$users = DB::table('users')
    ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.name', 'orders.total')
    ->get();

// Multiple joins
$data = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.name', 'profiles.bio', 'orders.total')
    ->get();
```

## Raw Expressions

```php
// Raw where clause
$users = DB::table('users')
    ->whereRaw('age > ? AND status = ?', [18, 'active'])
    ->get();

// Raw select
$stats = DB::table('orders')
    ->select(DB::raw('COUNT(*) as total_orders, SUM(total) as revenue'))
    ->first();

// Raw order by
$users = DB::table('users')
    ->orderByRaw('RAND()')
    ->limit(10)
    ->get();
```

## Transactions

```php
use AvelPress\Facades\DB;

// Manual transaction
DB::beginTransaction();

try {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => DB::getPdo()->lastInsertId()]);

    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}

// Transaction with closure
DB::transaction(function() {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => DB::getPdo()->lastInsertId()]);
});
```

## Pagination

```php
// Simple pagination
$users = DB::table('users')
    ->where('status', 'active')
    ->paginate(15); // 15 items per page

// Get current page from request
$page = $_GET['page'] ?? 1;
$users = DB::table('users')->paginate(15, $page);

// Custom pagination
$perPage = 20;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $perPage;

$users = DB::table('users')
    ->offset($offset)
    ->limit($perPage)
    ->get();

$total = DB::table('users')->count();
$totalPages = ceil($total / $perPage);
```

## WordPress Integration

### Working with WordPress Tables

```php
// Access WordPress core tables
$posts = DB::table('posts')
    ->where('post_status', 'publish')
    ->where('post_type', 'post')
    ->get();

$users = DB::table('users')
    ->join('usermeta', 'users.ID', '=', 'usermeta.user_id')
    ->where('usermeta.meta_key', 'wp_capabilities')
    ->get();
```

### Custom Table Prefixes

```php
// AvelPress automatically handles WordPress table prefixes
// But you can also specify custom prefixes for your plugin tables

// In your configuration
$prefix = $GLOBALS['wpdb']->prefix . 'myplugin_';

$data = DB::table($prefix . 'custom_table')->get();
```

### WordPress Hooks Integration

```php
// Use WordPress hooks with database operations
add_action('init', function() {
    $recentPosts = DB::table('posts')
        ->where('post_status', 'publish')
        ->where('post_date', '>', date('Y-m-d', strtotime('-7 days')))
        ->orderBy('post_date', 'desc')
        ->get();

    // Process recent posts
});
```

## Error Handling

```php
try {
    $result = DB::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
} catch (\Exception $e) {
    // Handle database error
    error_log('Database error: ' . $e->getMessage());

    // Return error response
    return new \WP_Error('db_error', 'Database operation failed');
}
```

## Performance Tips

### Query Optimization

```php
// Use select() to limit columns
$users = DB::table('users')
    ->select('id', 'name', 'email')  // Only fetch needed columns
    ->where('status', 'active')
    ->get();

// Use limit() for large datasets
$recentUsers = DB::table('users')
    ->orderBy('created_at', 'desc')
    ->limit(100)
    ->get();

// Use exists() instead of count() for existence checks
$hasActiveUsers = DB::table('users')
    ->where('status', 'active')
    ->exists();
```

### Caching Results

```php
// Cache database results
$cacheKey = 'active_users_count';
$count = wp_cache_get($cacheKey);

if ($count === false) {
    $count = DB::table('users')->where('status', 'active')->count();
    wp_cache_set($cacheKey, $count, '', 3600); // Cache for 1 hour
}
```

This database layer provides all the tools you need to work efficiently with data in your WordPress plugins and themes while maintaining clean, readable code.
