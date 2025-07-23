# Model API Reference

The AvelPress Model class provides an Eloquent-style ORM for WordPress database interactions. It offers a fluent interface for querying, creating, updating, and deleting records.

## Class: `Model`

**Namespace:** `AvelPress\Database\Eloquent\Model`

### Basic Usage

```php
use AvelPress\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email'];
}
```

## Properties

### Protected Properties

| Property      | Type   | Default | Description                            |
| ------------- | ------ | ------- | -------------------------------------- |
| `$table`      | string | null    | Table name (auto-generated if not set) |
| `$primaryKey` | string | 'id'    | Primary key column name                |
| `$fillable`   | array  | []      | Mass assignable attributes             |
| `$guarded`    | array  | ['*']   | Guarded attributes                     |
| `$hidden`     | array  | []      | Hidden attributes for serialization    |
| `$visible`    | array  | []      | Visible attributes for serialization   |
| `$appends`    | array  | []      | Accessor attributes to append          |
| `$prefix`     | string | ''      | Table prefix                           |

### Public Properties

| Property        | Type    | Default | Description                      |
| --------------- | ------- | ------- | -------------------------------- |
| `$timestamps`   | boolean | false   | Enable automatic timestamps      |
| `$incrementing` | boolean | true    | Primary key is auto-incrementing |

## Query Methods

### Finding Records

#### `find($id)`

Find a model by its primary key.

```php
$user = User::find(1);
$users = User::find([1, 2, 3]); // Multiple IDs
```

**Parameters:**

- `$id` (mixed): Primary key value or array of values

**Returns:** Model instance, Collection, or null

#### `findOrFail($id)`

Find a model by its primary key or throw an exception.

```php
$user = User::findOrFail(1);
```

**Parameters:**

- `$id` (mixed): Primary key value

**Returns:** Model instance
**Throws:** `ModelNotFoundException` if not found

#### `first()`

Get the first record matching the query.

```php
$user = User::where('status', 'active')->first();
```

**Returns:** Model instance or null

#### `firstOrFail()`

Get the first record or throw an exception.

```php
$user = User::where('email', 'test@example.com')->firstOrFail();
```

**Returns:** Model instance
**Throws:** `ModelNotFoundException` if not found

### Retrieving Multiple Records

#### `all($columns = ['*'])`

Get all records from the table.

```php
$users = User::all();
$users = User::all(['id', 'name', 'email']);
```

**Parameters:**

- `$columns` (array): Columns to select

**Returns:** Collection

#### `get($columns = ['*'])`

Execute the query and get the results.

```php
$activeUsers = User::where('status', 'active')->get();
```

**Parameters:**

- `$columns` (array): Columns to select

**Returns:** Collection

#### `paginate($perPage = 15, $page = null)`

Paginate the given query.

```php
$users = User::where('status', 'active')->paginate(20);
```

**Parameters:**

- `$perPage` (int): Items per page
- `$page` (int): Current page number

**Returns:** Paginator instance

### Query Constraints

#### `where($column, $operator = null, $value = null)`

Add a where clause to the query.

```php
User::where('status', 'active')->get();
User::where('age', '>', 18)->get();
User::where('name', 'like', '%john%')->get();
```

**Parameters:**

- `$column` (string): Column name
- `$operator` (string): Comparison operator
- `$value` (mixed): Value to compare

**Returns:** QueryBuilder instance

#### `whereIn($column, $values)`

Add a "where in" clause.

```php
User::whereIn('id', [1, 2, 3])->get();
```

#### `whereNull($column)` / `whereNotNull($column)`

Add null/not null where clauses.

```php
User::whereNull('deleted_at')->get();
User::whereNotNull('email_verified_at')->get();
```

#### `whereBetween($column, $values)`

Add a "where between" clause.

```php
User::whereBetween('age', [18, 65])->get();
```

### Ordering and Limiting

#### `orderBy($column, $direction = 'asc')`

Add an order by clause.

```php
User::orderBy('created_at', 'desc')->get();
```

#### `limit($value)` / `take($value)`

Limit the number of results.

```php
User::limit(10)->get();
User::take(5)->get();
```

#### `offset($value)` / `skip($value)`

Skip a number of results.

```php
User::offset(20)->limit(10)->get();
User::skip(10)->take(5)->get();
```

## Creating and Updating

### Creating Records

#### `create($attributes)`

Create a new record with mass assignment.

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

**Parameters:**

- `$attributes` (array): Attributes to set

**Returns:** Model instance

#### `firstOrCreate($attributes, $values = [])`

Find existing record or create new one.

```php
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'status' => 'active']
);
```

#### `updateOrCreate($attributes, $values = [])`

Update existing record or create new one.

```php
$user = User::updateOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Smith', 'status' => 'active']
);
```

### Updating Records

#### `update($attributes)`

Update the model with given attributes.

```php
$user = User::find(1);
$user->update(['name' => 'Jane Doe']);

// Mass update
User::where('status', 'pending')->update(['status' => 'active']);
```

#### `save()`

Save the model to the database.

```php
$user = new User;
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Update existing
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();
```

#### `increment($column, $amount = 1)`

Increment a column's value.

```php
$post = Post::find(1);
$post->increment('views');
$post->increment('views', 5);
```

#### `decrement($column, $amount = 1)`

Decrement a column's value.

```php
$user = User::find(1);
$user->decrement('credits');
$user->decrement('credits', 10);
```

## Deleting Records

#### `delete()`

Delete the model from the database.

```php
$user = User::find(1);
$user->delete();
```

#### `destroy($ids)`

Delete multiple models by their primary keys.

```php
User::destroy(1);
User::destroy([1, 2, 3]);
```

#### `forceDelete()`

Permanently delete a soft-deleted model.

```php
$user = User::withTrashed()->find(1);
$user->forceDelete();
```

## Soft Deletes

### Enabling Soft Deletes

```php
use AvelPress\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

### Soft Delete Methods

#### `withTrashed()`

Include soft-deleted models in results.

```php
$users = User::withTrashed()->get();
```

#### `onlyTrashed()`

Get only soft-deleted models.

```php
$deletedUsers = User::onlyTrashed()->get();
```

#### `restore()`

Restore a soft-deleted model.

```php
$user = User::withTrashed()->find(1);
$user->restore();
```

#### `trashed()`

Check if model is soft-deleted.

```php
if ($user->trashed()) {
    // Model is soft-deleted
}
```

## Relationships

### One to One

#### `hasOne($related, $foreignKey = null, $localKey = null)`

Define a one-to-one relationship.

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

#### `belongsTo($related, $foreignKey = null, $ownerKey = null)`

Define an inverse one-to-one relationship.

```php
class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### One to Many

#### `hasMany($related, $foreignKey = null, $localKey = null)`

Define a one-to-many relationship.

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

### Eager Loading

#### `with($relations)`

Eager load relationships.

```php
$users = User::with(['posts', 'profile'])->get();
$user = User::with('posts.comments')->find(1);
```

## Scopes

### Local Scopes

#### Defining Scopes

```php
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
```

#### Using Scopes

```php
$activeUsers = User::active()->get();
$premiumUsers = User::active()->ofType('premium')->get();
```

### Global Scopes

#### Adding Global Scopes

```php
class User extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function ($query) {
            $query->where('status', 'active');
        });
    }
}
```

## Accessors and Mutators

### Accessors

#### Defining Accessors

```php
class User extends Model
{
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? '/uploads/' . $this->avatar : '/default.png';
    }
}
```

#### Using Accessors

```php
$user = User::find(1);
echo $user->full_name; // Calls getFullNameAttribute()
echo $user->avatar_url; // Calls getAvatarUrlAttribute()
```

### Mutators

#### Defining Mutators

```php
class User extends Model
{
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = wp_hash_password($value);
    }
}
```

## Events

### Available Events

- `creating` - Before creating a new record
- `created` - After creating a new record
- `updating` - Before updating a record
- `updated` - After updating a record
- `saving` - Before saving (create or update)
- `saved` - After saving (create or update)
- `deleting` - Before deleting a record
- `deleted` - After deleting a record

### Using Events

```php
class User extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = wp_generate_uuid4();
        });

        static::created(function ($user) {
            // Send welcome email
            wp_mail($user->email, 'Welcome!', 'Welcome message');
        });

        static::deleting(function ($user) {
            // Delete related records
            $user->posts()->delete();
        });
    }
}
```

## Serialization

### Array Conversion

#### `toArray()`

Convert model to array.

```php
$user = User::find(1);
$array = $user->toArray();
```

#### `toJson($options = 0)`

Convert model to JSON.

```php
$user = User::find(1);
$json = $user->toJson();
```

### Controlling Serialization

#### Hidden Attributes

```php
class User extends Model
{
    protected $hidden = ['password', 'remember_token'];
}
```

#### Visible Attributes

```php
class User extends Model
{
    protected $visible = ['id', 'name', 'email'];
}
```

#### Appending Accessors

```php
class User extends Model
{
    protected $appends = ['full_name', 'avatar_url'];
}
```

## Collection Methods

When retrieving multiple models, you get a Collection instance with additional methods:

```php
$users = User::all();

// Filter collection
$activeUsers = $users->where('status', 'active');

// Map over collection
$names = $users->pluck('name');

// Group by attribute
$grouped = $users->groupBy('status');

// Sort collection
$sorted = $users->sortBy('created_at');
```

## Advanced Usage

### Raw Queries

```php
$users = User::whereRaw('age > ? AND status = ?', [18, 'active'])->get();
$stats = User::selectRaw('COUNT(*) as total, AVG(age) as avg_age')->first();
```

### Chunking Results

```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process each user
    }
});
```

### Exists Checks

```php
$exists = User::where('email', 'test@example.com')->exists();
$count = User::where('status', 'active')->count();
```

This Model API provides a comprehensive interface for database operations while maintaining WordPress compatibility and following Laravel conventions.
