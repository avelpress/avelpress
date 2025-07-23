# Basic Plugin Example

This example demonstrates how to build a complete WordPress plugin using AvelPress framework. We'll create a "Book Manager" plugin that manages a library of books with full CRUD operations.

## Plugin Structure

```
book-manager/
├── book-manager.php              # Main plugin file
├── app/
│   ├── Controllers/
│   │   └── BookController.php
│   ├── Models/
│   │   ├── Book.php
│   │   └── Category.php
│   ├── Http/
│   │   └── Resources/
│   │       └── BookResource.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── RouteServiceProvider.php
├── bootstrap/
│   └── providers.php
├── config/
│   └── app.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_create_categories_table.php
│       └── 2024_01_02_create_books_table.php
├── routes/
│   └── api.php
├── public/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
└── resources/
    └── views/
        └── admin/
            └── books.php
```

## Main Plugin File

**book-manager.php**

```php
<?php
/**
 * Plugin Name: Book Manager
 * Plugin URI: https://example.com/book-manager
 * Description: A complete book management system built with AvelPress framework
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: book-manager
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('BOOK_MANAGER_VERSION', '1.0.0');
define('BOOK_MANAGER_PLUGIN_FILE', __FILE__);
define('BOOK_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOOK_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include AvelPress framework
require_once __DIR__ . '/avelpress/src/AvelPress.php';

use AvelPress\AvelPress;

// Initialize AvelPress
$app = AvelPress::init('book-manager', [
    'base_path' => __DIR__,
]);

// Activation hook
register_activation_hook(__FILE__, function() {
    // Run migrations on activation
    $migrator = AvelPress::app('migrator');
    $migrator->run();

    // Create default data
    \App\Models\Category::firstOrCreate(['name' => 'Fiction']);
    \App\Models\Category::firstOrCreate(['name' => 'Non-Fiction']);
    \App\Models\Category::firstOrCreate(['name' => 'Science']);

    // Flush rewrite rules
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Add admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Book Manager',
        'Books',
        'manage_options',
        'book-manager',
        'book_manager_admin_page',
        'dashicons-book',
        30
    );
});

function book_manager_admin_page() {
    include BOOK_MANAGER_PLUGIN_DIR . 'resources/views/admin/books.php';
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_book-manager') {
        return;
    }

    wp_enqueue_style(
        'book-manager-admin',
        BOOK_MANAGER_PLUGIN_URL . 'public/css/admin.css',
        [],
        BOOK_MANAGER_VERSION
    );

    wp_enqueue_script(
        'book-manager-admin',
        BOOK_MANAGER_PLUGIN_URL . 'public/js/admin.js',
        ['jquery'],
        BOOK_MANAGER_VERSION,
        true
    );

    // Localize script with API endpoints
    wp_localize_script('book-manager-admin', 'bookManager', [
        'apiUrl' => rest_url('book-manager/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
});
```

## Configuration

**config/app.php**

```php
<?php

return [
    'name' => 'Book Manager',
    'version' => '1.0.0',
    'db_prefix' => 'bm_',
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],
    'upload' => [
        'max_size' => 2 * 1024 * 1024, // 2MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],
];
```

**bootstrap/providers.php**

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
];
```

## Service Providers

**app/Providers/AppServiceProvider.php**

```php
<?php

namespace App\Providers;

use AvelPress\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register custom services
    }

    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Add WordPress hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
    }

    public function enqueuePublicAssets()
    {
        // Enqueue public assets if needed
    }
}
```

**app/Providers/RouteServiceProvider.php**

```php
<?php

namespace App\Providers;

use AvelPress\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
```

## Database Migrations

**database/migrations/2024_01_01_create_categories_table.php**

```php
<?php

use AvelPress\Database\Migrations\Migration;
use AvelPress\Database\Schema\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function ($table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3498db');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
```

**database/migrations/2024_01_02_create_books_table.php**

```php
<?php

use AvelPress\Database\Migrations\Migration;
use AvelPress\Database\Schema\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('books', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('author');
            $table->string('isbn', 13)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->date('published_date')->nullable();
            $table->integer('pages')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('published_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
};
```

## Models

**app/Models/Category.php**

```php
<?php

namespace App\Models;

use AvelPress\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'active'
    ];

    public $timestamps = true;

    // Automatically generate slug
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = sanitize_title($value);
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Relationship: Category has many books
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // Get books count
    public function getBooksCountAttribute()
    {
        return $this->books()->count();
    }
}
```

**app/Models/Book.php**

```php
<?php

namespace App\Models;

use AvelPress\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'author',
        'isbn',
        'price',
        'published_date',
        'pages',
        'cover_image',
        'status',
        'category_id'
    ];

    public $timestamps = true;

    // Automatically generate slug
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = sanitize_title($value);
    }

    // Format price
    public function getPriceFormattedAttribute()
    {
        return $this->price ? '$' . number_format($this->price, 2) : 'Free';
    }

    // Get cover image URL
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return wp_upload_dir()['baseurl'] . '/book-covers/' . $this->cover_image;
        }
        return BOOK_MANAGER_PLUGIN_URL . 'public/images/default-cover.png';
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('author', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Relationship: Book belongs to category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

## Controllers

**app/Controllers/BookController.php**

```php
<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Http\Resources\BookResource;
use AvelPress\Routing\Controller;
use AvelPress\Http\Json\ResourceCollection;

class BookController extends Controller
{
    public function index($request)
    {
        $query = Book::with('category');

        // Filter by category
        if ($categoryId = $request->get_param('category_id')) {
            $query->byCategory($categoryId);
        }

        // Filter by status
        if ($status = $request->get_param('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->get_param('search')) {
            $query->search($search);
        }

        // Sorting
        $sortBy = $request->get_param('sort_by') ?: 'created_at';
        $sortOrder = $request->get_param('sort_order') ?: 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get_param('per_page') ?: 20, 100);
        $books = $query->paginate($perPage);

        return new ResourceCollection($books, BookResource::class);
    }

    public function show($request)
    {
        $id = $request->get_param('id');
        $book = Book::with('category')->find($id);

        if (!$book) {
            return new \WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        return new BookResource($book);
    }

    public function store($request)
    {
        // Validate required fields
        $title = $request->get_param('title');
        $author = $request->get_param('author');
        $categoryId = $request->get_param('category_id');

        if (!$title || !$author || !$categoryId) {
            return new \WP_Error(
                'missing_fields',
                'Title, author, and category are required',
                ['status' => 400]
            );
        }

        // Check if category exists
        if (!Category::find($categoryId)) {
            return new \WP_Error(
                'invalid_category',
                'Category not found',
                ['status' => 400]
            );
        }

        // Create book
        $book = Book::create([
            'title' => sanitize_text_field($title),
            'author' => sanitize_text_field($author),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'isbn' => sanitize_text_field($request->get_param('isbn')),
            'price' => floatval($request->get_param('price')),
            'published_date' => $request->get_param('published_date'),
            'pages' => intval($request->get_param('pages')),
            'status' => in_array($request->get_param('status'), ['draft', 'published', 'archived'])
                ? $request->get_param('status') : 'draft',
            'category_id' => $categoryId,
        ]);

        return new BookResource($book->load('category'));
    }

    public function update($request)
    {
        $id = $request->get_param('id');
        $book = Book::find($id);

        if (!$book) {
            return new \WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        // Update fields
        $updateData = [];

        if ($title = $request->get_param('title')) {
            $updateData['title'] = sanitize_text_field($title);
        }

        if ($author = $request->get_param('author')) {
            $updateData['author'] = sanitize_text_field($author);
        }

        if ($request->has_param('description')) {
            $updateData['description'] = sanitize_textarea_field($request->get_param('description'));
        }

        if ($categoryId = $request->get_param('category_id')) {
            if (!Category::find($categoryId)) {
                return new \WP_Error('invalid_category', 'Category not found', ['status' => 400]);
            }
            $updateData['category_id'] = $categoryId;
        }

        // Add other fields...
        $book->update($updateData);

        return new BookResource($book->load('category'));
    }

    public function destroy($request)
    {
        $id = $request->get_param('id');
        $book = Book::find($id);

        if (!$book) {
            return new \WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        // Delete cover image if exists
        if ($book->cover_image) {
            $uploadDir = wp_upload_dir();
            $imagePath = $uploadDir['basedir'] . '/book-covers/' . $book->cover_image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $book->delete();

        return ['message' => 'Book deleted successfully'];
    }

    public function categories($request)
    {
        $categories = Category::active()->orderBy('name')->get();
        return $categories;
    }
}
```

## JSON Resources

**app/Http/Resources/BookResource.php**

```php
<?php

namespace App\Http\Resources;

use AvelPress\Http\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'price' => $this->price,
            'price_formatted' => $this->price_formatted,
            'published_date' => $this->published_date,
            'pages' => $this->pages,
            'cover_image_url' => $this->cover_image_url,
            'status' => $this->status,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'color' => $this->category->color,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

## Routes

**routes/api.php**

```php
<?php

use App\Controllers\BookController;
use AvelPress\Facades\Route;

Route::prefix('book-manager/v1')->group(function () {

    // Books endpoints
    Route::get('books', [BookController::class, 'index']);
    Route::post('books', [BookController::class, 'store']);
    Route::get('books/{id}', [BookController::class, 'show']);
    Route::put('books/{id}', [BookController::class, 'update']);
    Route::delete('books/{id}', [BookController::class, 'destroy']);

    // Categories endpoint
    Route::get('categories', [BookController::class, 'categories']);

});
```

## Admin Interface

**resources/views/admin/books.php**

```php
<div class="wrap">
    <h1>Book Manager</h1>

    <div id="book-manager-app">
        <div class="book-manager-header">
            <div class="book-manager-search">
                <input type="text" id="book-search" placeholder="Search books..." />
                <select id="category-filter">
                    <option value="">All Categories</option>
                </select>
                <select id="status-filter">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <button id="add-book-btn" class="button button-primary">Add New Book</button>
        </div>

        <div id="books-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="books-table-body">
                    <!-- Books will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="pagination-container"></div>
    </div>

    <!-- Add/Edit Book Modal -->
    <div id="book-modal" class="book-modal" style="display: none;">
        <div class="book-modal-content">
            <span class="book-modal-close">&times;</span>
            <h2 id="modal-title">Add New Book</h2>
            <form id="book-form">
                <input type="hidden" id="book-id" />

                <table class="form-table">
                    <tr>
                        <th><label for="book-title">Title *</label></th>
                        <td><input type="text" id="book-title" required /></td>
                    </tr>
                    <tr>
                        <th><label for="book-author">Author *</label></th>
                        <td><input type="text" id="book-author" required /></td>
                    </tr>
                    <tr>
                        <th><label for="book-category">Category *</label></th>
                        <td><select id="book-category" required></select></td>
                    </tr>
                    <tr>
                        <th><label for="book-description">Description</label></th>
                        <td><textarea id="book-description" rows="4"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="book-isbn">ISBN</label></th>
                        <td><input type="text" id="book-isbn" /></td>
                    </tr>
                    <tr>
                        <th><label for="book-price">Price</label></th>
                        <td><input type="number" id="book-price" step="0.01" /></td>
                    </tr>
                    <tr>
                        <th><label for="book-pages">Pages</label></th>
                        <td><input type="number" id="book-pages" /></td>
                    </tr>
                    <tr>
                        <th><label for="book-published-date">Published Date</label></th>
                        <td><input type="date" id="book-published-date" /></td>
                    </tr>
                    <tr>
                        <th><label for="book-status">Status</label></th>
                        <td>
                            <select id="book-status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Save Book</button>
                    <button type="button" class="button" onclick="closeBookModal()">Cancel</button>
                </p>
            </form>
        </div>
    </div>
</div>
```

## Frontend JavaScript

**public/js/admin.js**

```javascript
jQuery(document).ready(function ($) {
  let currentPage = 1;
  let books = [];
  let categories = [];

  // Load initial data
  loadCategories();
  loadBooks();

  // Event handlers
  $("#book-search").on("input", debounce(loadBooks, 300));
  $("#category-filter, #status-filter").on("change", loadBooks);
  $("#add-book-btn").on("click", openAddBookModal);
  $("#book-form").on("submit", saveBook);
  $(".book-modal-close").on("click", closeBookModal);

  // Load categories
  function loadCategories() {
    $.get(bookManager.apiUrl + "categories", {
      _wpnonce: bookManager.nonce,
    }).done(function (data) {
      categories = data;
      populateCategories();
    });
  }

  // Populate category dropdowns
  function populateCategories() {
    const $categoryFilter = $("#category-filter");
    const $bookCategory = $("#book-category");

    $categoryFilter.empty().append('<option value="">All Categories</option>');
    $bookCategory.empty().append('<option value="">Select Category</option>');

    categories.forEach(function (category) {
      const option = `<option value="${category.id}">${category.name}</option>`;
      $categoryFilter.append(option);
      $bookCategory.append(option);
    });
  }

  // Load books
  function loadBooks() {
    const params = {
      _wpnonce: bookManager.nonce,
      page: currentPage,
      per_page: 20,
      search: $("#book-search").val(),
      category_id: $("#category-filter").val(),
      status: $("#status-filter").val(),
    };

    $.get(bookManager.apiUrl + "books", params).done(function (response) {
      books = response.data;
      renderBooksTable();
      renderPagination(response.meta);
    });
  }

  // Render books table
  function renderBooksTable() {
    const $tbody = $("#books-table-body");
    $tbody.empty();

    books.forEach(function (book) {
      const row = `
                <tr>
                    <td><img src="${book.cover_image_url}" width="40" height="60" /></td>
                    <td><strong>${book.title}</strong></td>
                    <td>${book.author}</td>
                    <td><span class="category-badge" style="background: ${book.category.color}">${book.category.name}</span></td>
                    <td><span class="status-${book.status}">${book.status}</span></td>
                    <td>${book.price_formatted}</td>
                    <td>
                        <button class="button button-small" onclick="editBook(${book.id})">Edit</button>
                        <button class="button button-small button-link-delete" onclick="deleteBook(${book.id})">Delete</button>
                    </td>
                </tr>
            `;
      $tbody.append(row);
    });
  }

  // Save book
  function saveBook(e) {
    e.preventDefault();

    const bookId = $("#book-id").val();
    const url = bookManager.apiUrl + "books" + (bookId ? "/" + bookId : "");
    const method = bookId ? "PUT" : "POST";

    const data = {
      title: $("#book-title").val(),
      author: $("#book-author").val(),
      category_id: $("#book-category").val(),
      description: $("#book-description").val(),
      isbn: $("#book-isbn").val(),
      price: $("#book-price").val(),
      pages: $("#book-pages").val(),
      published_date: $("#book-published-date").val(),
      status: $("#book-status").val(),
      _wpnonce: bookManager.nonce,
    };

    $.ajax({
      url: url,
      method: method,
      data: data,
    })
      .done(function () {
        closeBookModal();
        loadBooks();
        alert("Book saved successfully!");
      })
      .fail(function () {
        alert("Error saving book. Please try again.");
      });
  }

  // Global functions for onclick handlers
  window.editBook = function (id) {
    const book = books.find((b) => b.id === id);
    if (book) {
      populateBookForm(book);
      openEditBookModal();
    }
  };

  window.deleteBook = function (id) {
    if (confirm("Are you sure you want to delete this book?")) {
      $.ajax({
        url: bookManager.apiUrl + "books/" + id,
        method: "DELETE",
        data: { _wpnonce: bookManager.nonce },
      }).done(function () {
        loadBooks();
        alert("Book deleted successfully!");
      });
    }
  };

  // Modal functions
  function openAddBookModal() {
    $("#modal-title").text("Add New Book");
    $("#book-form")[0].reset();
    $("#book-id").val("");
    $("#book-modal").show();
  }

  function openEditBookModal() {
    $("#modal-title").text("Edit Book");
    $("#book-modal").show();
  }

  window.closeBookModal = function () {
    $("#book-modal").hide();
  };

  function populateBookForm(book) {
    $("#book-id").val(book.id);
    $("#book-title").val(book.title);
    $("#book-author").val(book.author);
    $("#book-category").val(book.category.id);
    $("#book-description").val(book.description);
    $("#book-isbn").val(book.isbn);
    $("#book-price").val(book.price);
    $("#book-pages").val(book.pages);
    $("#book-published-date").val(book.published_date);
    $("#book-status").val(book.status);
  }

  // Utility functions
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = function () {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
});
```

## API Usage Examples

Once the plugin is activated, you can use the REST API endpoints:

```bash
# Get all books
curl "https://yoursite.com/wp-json/book-manager/v1/books"

# Get books with filters
curl "https://yoursite.com/wp-json/book-manager/v1/books?category_id=1&status=published&search=fiction"

# Get specific book
curl "https://yoursite.com/wp-json/book-manager/v1/books/1"

# Create new book
curl -X POST "https://yoursite.com/wp-json/book-manager/v1/books" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "The Great Gatsby",
    "author": "F. Scott Fitzgerald",
    "category_id": 1,
    "description": "A classic American novel",
    "price": 12.99,
    "status": "published"
  }'

# Update book
curl -X PUT "https://yoursite.com/wp-json/book-manager/v1/books/1" \
  -H "Content-Type: application/json" \
  -d '{"price": 15.99, "status": "published"}'

# Delete book
curl -X DELETE "https://yoursite.com/wp-json/book-manager/v1/books/1"

# Get categories
curl "https://yoursite.com/wp-json/book-manager/v1/categories"
```

This complete example demonstrates how AvelPress enables you to build sophisticated WordPress plugins with clean, maintainable code using Laravel-inspired patterns and structures.
