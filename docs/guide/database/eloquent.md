# Eloquent Models

AvelPress provides an Eloquent-style ORM that makes it easy to work with your database. Each database table has a corresponding "Model" that is used to interact with that table.

## Defining Models

### Basic Model

Create a model by extending the base `Model` class:

```php
<?php

namespace App\Models;

use AvelPress\Database\Eloquent\Model;

class User extends Model
{
    // The table associated with the model
    protected $table = 'users';

    // The primary key for the model
    protected $primaryKey = 'id';

    // Indicates if the model should be timestamped
    public $timestamps = true;

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // The attributes that should be hidden for arrays
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

### Table Names

By convention, the table name is the plural form of the model name in lowercase. If your table name differs, specify it explicitly:

```php
class User extends Model
{
    protected $table = 'custom_users_table';
}
```

### Primary Keys

AvelPress assumes your table has a primary key column named `id`. If different:

```php
class User extends Model
{
    protected $primaryKey = 'user_id';

    // If your primary key is not an incrementing integer
    public $incrementing = false;

    // If your primary key is not an integer
    protected $keyType = 'string';
}
```

## Mass Assignment

### Fillable Attributes

Define which attributes can be mass-assigned:

```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
    ];
}

// Now you can use mass assignment
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);
```

### Guarded Attributes

Alternatively, define which attributes should be protected:

```php
class User extends Model
{
    // Everything except these can be mass-assigned
    protected $guarded = [
        'id',
        'password',
    ];
}
```

## Timestamps

### Automatic Timestamps

Enable automatic timestamp management:

```php
class Post extends Model
{
    public $timestamps = true;

    // Customize timestamp column names if needed
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
```

### Custom Timestamp Format

```php
class Post extends Model
{
    public $timestamps = true;

    protected function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
```

## Retrieving Models

### All Models

```php
// Get all users
$users = User::all();

// Get all with specific columns
$users = User::all(['name', 'email']);
```

### Finding Models

```php
// Find by primary key
$user = User::find(1);

// Find or fail (throws exception if not found)
$user = User::findOrFail(1);

// Find by multiple IDs
$users = User::find([1, 2, 3]);

// First match
$user = User::where('email', 'john@example.com')->first();

// First or fail
$user = User::where('email', 'john@example.com')->firstOrFail();
```

### Query Builder Methods

Models provide access to all query builder methods:

```php
// Where clauses
$activeUsers = User::where('status', 'active')->get();

// Multiple conditions
$users = User::where('status', 'active')
    ->where('age', '>', 18)
    ->get();

// Order and limit
$recentUsers = User::orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Counting
$count = User::where('status', 'active')->count();

// Pagination
$users = User::paginate(15);
```

## Creating and Updating

### Creating Models

```php
// Method 1: New instance
$user = new User;
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Method 2: Mass assignment with create()
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Method 3: firstOrCreate (find or create)
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'status' => 'active']
);

// Method 4: updateOrCreate
$user = User::updateOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'status' => 'active']
);
```

### Updating Models

```php
// Update single model
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Mass update
$user->update(['name' => 'Jane Doe', 'status' => 'inactive']);

// Update multiple models
User::where('status', 'pending')
    ->update(['status' => 'active']);
```

## Deleting Models

### Delete Single Model

```php
$user = User::find(1);
$user->delete();

// Or delete by ID
User::destroy(1);

// Delete multiple
User::destroy([1, 2, 3]);
```

### Delete by Query

```php
// Delete all inactive users
User::where('status', 'inactive')->delete();
```

### Soft Deletes

Enable soft deletes to mark records as deleted without actually removing them:

```php
<?php

namespace App\Models;

use AvelPress\Database\Eloquent\Model;
use AvelPress\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email'];
}
```

```php
// This will set deleted_at timestamp instead of removing the record
$user = User::find(1);
$user->delete();

// Include soft deleted models
$users = User::withTrashed()->get();

// Only soft deleted models
$deletedUsers = User::onlyTrashed()->get();

// Restore soft deleted model
$user = User::withTrashed()->find(1);
$user->restore();

// Force delete (permanent)
$user->forceDelete();
```

## Query Scopes

### Local Scopes

Define reusable query constraints:

```php
class User extends Model
{
    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope with parameters
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope for popular users
    public function scopePopular($query)
    {
        return $query->where('followers_count', '>', 1000);
    }
}
```

```php
// Use scopes
$activeUsers = User::active()->get();

$premiumUsers = User::active()->ofType('premium')->get();

$popularActiveUsers = User::active()->popular()->get();
```

### Global Scopes

Apply constraints to all queries for a model:

```php
class User extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Always exclude banned users
        static::addGlobalScope('notBanned', function ($query) {
            $query->where('status', '!=', 'banned');
        });
    }
}
```

## Accessors and Mutators

### Accessors

Transform data when retrieving from the database:

```php
class User extends Model
{
    // Get first name from full name
    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    // Format email as lowercase
    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }

    // Get full URL for avatar
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return wp_upload_dir()['baseurl'] . '/avatars/' . $this->avatar;
        }
        return null;
    }
}
```

```php
$user = User::find(1);
echo $user->first_name; // Calls getFirstNameAttribute()
echo $user->avatar_url; // Calls getAvatarUrlAttribute()
```

### Mutators

Transform data when saving to the database:

```php
class User extends Model
{
    // Hash password automatically
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = wp_hash_password($value);
    }

    // Store email as lowercase
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Auto-generate slug from title
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = sanitize_title($value);
    }
}
```

```php
$user = new User;
$user->password = 'plaintext'; // Automatically hashed
$user->email = 'JOHN@EXAMPLE.COM'; // Stored as lowercase
$user->save();
```

## Serialization

### Array / JSON Conversion

```php
$user = User::find(1);

// Convert to array
$array = $user->toArray();

// Convert to JSON
$json = $user->toJson();

// When returning from API routes, automatic conversion happens
return $user; // Automatically converts to JSON
```

### Hiding Attributes

```php
class User extends Model
{
    protected $hidden = ['password', 'remember_token'];

    // Or specify visible attributes
    protected $visible = ['name', 'email'];
}
```

### Appending Accessors

```php
class User extends Model
{
    protected $appends = ['first_name', 'avatar_url'];

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? '/avatars/' . $this->avatar : null;
    }
}
```

## Model Events

### Available Events

```php
class User extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Before creating
        static::creating(function ($user) {
            $user->uuid = wp_generate_uuid4();
        });

        // After creating
        static::created(function ($user) {
            // Send welcome email
            wp_mail($user->email, 'Welcome!', 'Welcome to our site!');
        });

        // Before updating
        static::updating(function ($user) {
            // Log the change
            error_log("User {$user->id} is being updated");
        });

        // After updating
        static::updated(function ($user) {
            // Clear cache
            wp_cache_delete("user_{$user->id}");
        });

        // Before deleting
        static::deleting(function ($user) {
            // Delete related data
            $user->posts()->delete();
        });
    }
}
```

## Complete Example

Here's a complete User model with various features:

```php
<?php

namespace App\Models;

use AvelPress\Database\Eloquent\Model;
use AvelPress\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'first_name',
        'avatar_url',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // Accessors
    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return wp_upload_dir()['baseurl'] . '/avatars/' . $this->avatar;
        }
        return get_avatar_url($this->email);
    }

    // Mutators
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = wp_hash_password($value);
    }

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = wp_generate_uuid4();
        });

        static::created(function ($user) {
            // Create default profile
            $user->profile()->create(['bio' => '']);
        });
    }
}
```

This Eloquent implementation provides a powerful and familiar way to work with your data while staying true to WordPress conventions and best practices.
