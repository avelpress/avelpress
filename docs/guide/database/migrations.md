# Migrations

Database migrations are version control for your database, allowing you to modify your database schema in a structured and organized way. AvelPress migrations are inspired by Laravel's migration system.

## Introduction

Migrations allow you to:

- Create and modify database tables
- Add and remove columns
- Create indexes and foreign keys
- Seed initial data
- Version control your database schema

## Creating Migrations

### Migration Files

Migration files are stored in the `database/migrations` directory and follow this naming convention:

```
YYYY_MM_DD_HHMMSS_description_of_migration.php
```

### Basic Migration Structure

```php
<?php

use AvelPress\Database\Migrations\Migration;
use AvelPress\Database\Schema\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
```

## Table Operations

### Creating Tables

#### `Schema::create()`

Create a new table.

```php
Schema::create('posts', function ($table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('status')->default('draft');
    $table->timestamps();
});
```

#### Table Options

```php
Schema::create('posts', function ($table) {
    $table->id();
    $table->string('title');
    // ... other columns
}, [
    'engine' => 'InnoDB',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
]);
```

### Modifying Tables

#### `Schema::table()`

Modify an existing table.

```php
Schema::table('users', function ($table) {
    $table->string('phone')->nullable();
    $table->dropColumn('old_column');
    $table->renameColumn('old_name', 'new_name');
});
```

### Dropping Tables

#### `Schema::drop()` / `Schema::dropIfExists()`

```php
// Drop table (will fail if table doesn't exist)
Schema::drop('posts');

// Drop table if it exists
Schema::dropIfExists('posts');
```

## Column Types

### String Columns

```php
$table->string('name');                    // VARCHAR(255)
$table->string('name', 100);               // VARCHAR(100)
$table->text('description');               // TEXT
$table->longText('content');               // LONGTEXT
$table->char('code', 10);                  // CHAR(10)
```

### Numeric Columns

```php
$table->integer('views');                  // INT
$table->bigInteger('big_number');          // BIGINT
$table->smallInteger('small_number');      // SMALLINT
$table->decimal('price', 8, 2);            // DECIMAL(8,2)
$table->float('rating', 3, 1);             // FLOAT(3,1)
$table->double('coordinates', 15, 8);      // DOUBLE(15,8)
```

### Date and Time Columns

```php
$table->date('birth_date');                // DATE
$table->time('start_time');                // TIME
$table->datetime('created_at');            // DATETIME
$table->timestamp('updated_at');           // TIMESTAMP
$table->timestamps();                      // created_at & updated_at
```

### Boolean and Binary

```php
$table->boolean('is_active');              // BOOLEAN
$table->binary('data');                    // BLOB
```

### Special Columns

```php
$table->id();                              // Auto-increment primary key
$table->uuid('uuid');                      // UUID column
$table->json('metadata');                  // JSON column (MySQL 5.7+)
$table->enum('status', ['draft', 'published', 'archived']);
```

## Column Modifiers

### Nullable and Default Values

```php
$table->string('name')->nullable();        // Allow NULL
$table->string('status')->default('active'); // Default value
$table->integer('views')->default(0);
$table->timestamp('created_at')->useCurrent(); // Use CURRENT_TIMESTAMP
```

### Indexes

```php
$table->string('email')->unique();         // Unique index
$table->string('name')->index();           // Regular index
$table->index(['user_id', 'created_at']);  // Composite index
$table->index('email', 'idx_user_email');  // Named index
```

### Auto Increment

```php
$table->id();                              // Auto-increment primary key
$table->bigInteger('id')->autoIncrement()->primary(); // Custom auto-increment
```

### Comments

```php
$table->string('name')->comment('User full name');
$table->integer('views')->comment('Page view count');
```

## Foreign Key Constraints

### Basic Foreign Keys

```php
// Add foreign key
$table->foreignId('user_id')->constrained();

// Custom foreign key
$table->foreignId('category_id')->constrained('categories');

// Foreign key with custom column
$table->unsignedBigInteger('author_id');
$table->foreign('author_id')->references('id')->on('users');
```

### Foreign Key Actions

```php
$table->foreignId('user_id')
    ->constrained()
    ->onUpdate('cascade')
    ->onDelete('cascade');

$table->foreignId('category_id')
    ->constrained()
    ->onDelete('set null');
```

### Dropping Foreign Keys

```php
Schema::table('posts', function ($table) {
    $table->dropForeign(['user_id']);
    $table->dropForeign('posts_user_id_foreign'); // By name
});
```

## Indexes

### Creating Indexes

```php
Schema::table('users', function ($table) {
    $table->index('email');                 // Single column
    $table->index(['first_name', 'last_name']); // Composite
    $table->unique('username');             // Unique index
    $table->index('created_at', 'idx_created'); // Named index
});
```

### Dropping Indexes

```php
Schema::table('users', function ($table) {
    $table->dropIndex(['email']);          // By column
    $table->dropIndex('users_email_index'); // By name
    $table->dropUnique(['username']);      // Drop unique
});
```

### Index Types

```php
$table->index('title');                    // Regular index
$table->unique('email');                   // Unique index
$table->primary(['id', 'type']);           // Composite primary key
$table->fullText('content');               // Full-text index (MySQL)
```

## Column Operations

### Adding Columns

```php
Schema::table('users', function ($table) {
    $table->string('phone')->nullable()->after('email');
    $table->text('bio')->after('name');
    $table->timestamp('verified_at')->nullable();
});
```

### Modifying Columns

```php
Schema::table('users', function ($table) {
    $table->string('name', 100)->change();  // Change length
    $table->string('email')->nullable()->change(); // Make nullable
    $table->renameColumn('name', 'full_name');
});
```

### Dropping Columns

```php
Schema::table('users', function ($table) {
    $table->dropColumn('phone');
    $table->dropColumn(['temp1', 'temp2']); // Multiple columns
});
```

## WordPress Integration

### Table Prefixes

AvelPress automatically handles WordPress table prefixes:

```php
// This creates {wp_prefix}my_custom_table
Schema::create('my_custom_table', function ($table) {
    $table->id();
    $table->string('name');
});
```

### WordPress-specific Columns

```php
Schema::create('posts', function ($table) {
    $table->id();
    $table->string('post_title');
    $table->longText('post_content');
    $table->string('post_status', 20)->default('publish');
    $table->string('post_type', 20)->default('post');
    $table->unsignedBigInteger('post_author');
    $table->datetime('post_date');
    $table->datetime('post_modified');

    // Foreign key to WordPress users table
    $table->foreign('post_author')->references('ID')->on('users');
});
```

### Integration with WordPress Tables

```php
Schema::create('user_profiles', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->text('bio')->nullable();
    $table->string('website')->nullable();
    $table->timestamps();

    // Reference WordPress users table
    $table->foreign('user_id')->references('ID')->on('users')->onDelete('cascade');
    $table->unique('user_id');
});
```

## Running Migrations

### Automatic Migration

Migrations can be run automatically when your plugin is activated:

```php
// In your main plugin file
register_activation_hook(__FILE__, function() {
    $migrator = AvelPress::app('migrator');
    $migrator->run();
});
```

### Manual Migration

```php
// In your service provider or initialization code
$migrator = AvelPress::app('migrator');
$migrator->run();
```

### Migration Status

```php
$migrator = AvelPress::app('migrator');

// Check if migrations are pending
if ($migrator->hasPendingMigrations()) {
    // Run pending migrations
    $migrator->run();
}

// Get migration status
$status = $migrator->getStatus();
```

## Advanced Features

### Conditional Migrations

```php
public function up()
{
    // Only create if table doesn't exist
    if (!Schema::hasTable('custom_table')) {
        Schema::create('custom_table', function ($table) {
            $table->id();
            $table->string('name');
        });
    }

    // Only add column if it doesn't exist
    if (!Schema::hasColumn('users', 'phone')) {
        Schema::table('users', function ($table) {
            $table->string('phone')->nullable();
        });
    }
}
```

### Data Seeding in Migrations

```php
public function up()
{
    Schema::create('categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->timestamps();
    });

    // Seed initial data
    DB::table('categories')->insert([
        ['name' => 'Technology', 'slug' => 'technology', 'created_at' => now()],
        ['name' => 'Business', 'slug' => 'business', 'created_at' => now()],
        ['name' => 'Design', 'slug' => 'design', 'created_at' => now()],
    ]);
}
```

### Raw SQL in Migrations

```php
public function up()
{
    // Create table with raw SQL
    DB::statement('CREATE TABLE custom_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Add custom index
    DB::statement('CREATE INDEX idx_custom ON custom_table (data->"$.type")');
}
```

## Best Practices

### 1. Always Provide Down Methods

```php
public function up()
{
    Schema::create('posts', function ($table) {
        // Table definition
    });
}

public function down()
{
    Schema::dropIfExists('posts');
}
```

### 2. Use Descriptive Names

```php
// Good
2024_01_15_143000_create_user_profiles_table.php
2024_01_15_144500_add_phone_to_users_table.php

// Bad
2024_01_15_143000_migration.php
2024_01_15_144500_update_users.php
```

### 3. Make Migrations Reversible

```php
public function up()
{
    Schema::table('users', function ($table) {
        $table->string('phone')->nullable();
    });
}

public function down()
{
    Schema::table('users', function ($table) {
        $table->dropColumn('phone');
    });
}
```

### 4. Handle Large Data Sets

```php
public function up()
{
    Schema::table('users', function ($table) {
        $table->string('full_name')->nullable();
    });

    // Update in chunks for large tables
    User::chunk(1000, function ($users) {
        foreach ($users as $user) {
            $user->update([
                'full_name' => $user->first_name . ' ' . $user->last_name
            ]);
        }
    });
}
```

### 5. Test Migrations

Always test your migrations in a development environment before deploying:

```php
// Test up migration
$migrator->run();

// Test down migration
$migrator->rollback();
```

This migration system provides a robust way to manage your database schema changes while maintaining compatibility with WordPress conventions and best practices.
