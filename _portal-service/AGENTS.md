<laravel-boost-guidelines>
=== .ai/custom rules ===

=== custom rules ===

# Project Architecture Guidelines

## Overview

Aplikasi ini menggunakan pola arsitektur berlapis: **Controller → Usecase → View**.
Tidak menggunakan Repository Pattern. Semua logika query langsung di Usecase menggunakan Query Builder.

---

## Layer: Controller (`app/Http/Controllers/`)

### Tanggung Jawab

- Menerima request HTTP
- Meneruskan data ke Usecase
- Mengembalikan View atau RedirectResponse
- **Tidak boleh** mengandung logika bisnis atau query DB

### Struktur Wajib

```php
class UserController extends Controller
{
    protected array $page = [
        'route' => 'user',
        'title' => 'Pengguna Aplikasi',
    ];

    protected string $baseRedirect;

    public function __construct(
        protected UserUsecase $usecase
    ) {
        $this->baseRedirect = 'admin/' . $this->page['route'];
    }
}
```

### Pola Method

**index** — tampilkan list dengan filter dari request:
```php
public function index(Request $request): View|Response
{
    $data = $this->usecase->getAll([
        'keywords' => $request->get('keywords'),
    ]);
    $data = $data['data']['list'] ?? [];

    return view('_admin.users.index', [
        'data' => $data,
        'page' => $this->page,
        'keywords' => $request->get('keywords'),
    ]);
}
```

**doCreate / doUpdate** — proses form submission:
```php
public function doCreate(Request $request): RedirectResponse
{
    $process = $this->usecase->create(data: $request);

    if ($process['success']) {
        return redirect()->route('admin.users.index')
            ->with('success', ResponseConst::SUCCESS_MESSAGE_CREATED);
    }

    return redirect()->back()->withInput()
        ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
}
```

**detail / update** — fetch data by ID, redirect jika tidak ditemukan:
```php
public function detail(int $id): View|RedirectResponse|Response
{
    $data = $this->usecase->getByID($id);

    if (empty($data['data'])) {
        return redirect()->intended($this->baseRedirect)
            ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    return view('_admin.users.detail', [
        'data' => (object) $data['data'],
        'page' => $this->page,
    ]);
}
```

### Aturan

- Inject Usecase via constructor property promotion
- Gunakan `$this->page` array untuk info halaman (diteruskan ke view)
- Return type harus dideklarasikan eksplisit (`View|RedirectResponse|Response`)
- Gunakan named arguments saat panggil usecase: `create(data: $request)`
- Gunakan `ResponseConst` untuk pesan sukses/error, jangan hardcode string

---

## Layer: Usecase (`app/Usecase/`)

### Tanggung Jawab

- Semua logika bisnis
- Validasi input (`Validator::make`)
- Query database via **Query Builder** (`DB::table()`) — **bukan Eloquent Model**
- Wrap mutasi dengan `DB::beginTransaction()` / `DB::commit()` / `DB::rollback()`
- Return format standar via `Response::build*()` methods

### Base Class

```php
// Semua Usecase extends ini
abstract class Usecase
{
    public string $className;
}
```

### Pola getAll dengan filter & pagination

```php
public function getAll(array $filterData = []): array
{
    try {
        $query = DB::table(DatabaseConst::TASK . ' as t')
            ->leftJoin(DatabaseConst::TASK_CATEGORY . ' as tc', 't.task_category_id', '=', 'tc.id')
            ->select('t.*', 'tc.name as category_name')
            ->whereNull('t.deleted_at')
            ->when($filterData['keywords'] ?? false, function ($query, $keywords) {
                return $query->where('t.title', 'like', '%' . $keywords . '%');
            })
            ->orderBy('t.created_at', 'desc');

        if (!empty($filterData['no_pagination'])) {
            $data = $query->get();
        } else {
            $data = $query->paginate(20);
            if (!empty($filterData)) {
                $data->appends($filterData);
            }
        }

        return Response::buildSuccess(['list' => $data], ResponseConst::HTTP_SUCCESS);
    } catch (Exception $e) {
        Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);
        return Response::buildErrorService($e->getMessage());
    }
}
```

### Pola getByID

```php
public function getByID(int $id): array
{
    try {
        $data = DB::table(DatabaseConst::USER)
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        return Response::buildSuccess(data: collect($data)->toArray());
    } catch (Exception $e) {
        Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);
        return Response::buildErrorService($e->getMessage());
    }
}
```

### Pola create

```php
public function create(Request $data): array
{
    $validator = Validator::make($data->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
    ]);

    $validator->validate();

    DB::beginTransaction();
    try {
        DB::table(DatabaseConst::USER)->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'created_by' => Auth::user()?->id,
            'created_at' => now(),
        ]);

        DB::commit();
        return Response::buildSuccessCreated();
    } catch (Exception $e) {
        DB::rollback();
        Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);
        return Response::buildErrorService($e->getMessage());
    }
}
```

### Pola update

```php
public function update(Request $data, int $id): array
{
    $validator = Validator::make($data->all(), ['name' => 'required']);
    $validator->validate();

    DB::beginTransaction();
    try {
        $payload = $data->only(['name', 'email']);
        $payload['updated_by'] = Auth::user()?->id;
        $payload['updated_at'] = now();

        DB::table(DatabaseConst::USER)->where('id', $id)->update($payload);
        DB::commit();

        return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_UPDATED);
    } catch (Exception $e) {
        DB::rollback();
        Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);
        return Response::buildErrorService($e->getMessage());
    }
}
```

### Pola delete (soft delete)

```php
public function delete(int $id): array
{
    DB::beginTransaction();
    try {
        $delete = DB::table(DatabaseConst::USER)->where('id', $id)->update([
            'deleted_by' => Auth::user()?->id,
            'deleted_at' => now(),
        ]);

        if (!$delete) {
            DB::rollback();
            throw new Exception('FAILED DELETE DATA');
        }

        DB::commit();
        return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_DELETED);
    } catch (Exception $e) {
        DB::rollback();
        Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);
        return Response::buildErrorService($e->getMessage());
    }
}
```

### Aturan Query Builder

- Selalu gunakan `DB::table(DatabaseConst::TABLE_NAME)` — referensi nama tabel via `DatabaseConst`, jangan hardcode string tabel
- Soft delete: filter dengan `->whereNull('deleted_at')`, update kolom `deleted_at` & `deleted_by`
- Audit trail: isi `created_by`, `updated_by`, `deleted_by` dari `Auth::user()?->id`
- Isi `created_at` dan `updated_at` secara manual dengan `now()` (tidak ada Eloquent auto-timestamp)
- Join: gunakan alias tabel (`as t`, `as tc`) agar tidak ambigu pada query yang kompleks
- Cast tipe data secara manual jika perlu (tidak ada Eloquent casts): `Carbon::parse($item->date)`
- `collect($data)->toArray()` untuk konversi stdClass hasil `->first()` ke array
- `$data->only([...])` untuk ambil field spesifik dari Request saat insert/update

### Format Response

Selalu return array via `App\Http\Presenter\Response`:
```php
Response::buildSuccess($data, $code)        // 200 sukses
Response::buildSuccessCreated($data)         // 201 created
Response::buildErrorService($message)        // 500 error
Response::buildErrorNotFound($message)       // 404 not found
Response::buildError($code, $message, $data) // custom error
```

---

## Layer: View (`resources/views/`)

### Struktur Direktori

```
resources/views/
├── _admin/           # Views untuk role admin

│   ├── _layout/      # Layout, partial, icons

│   ├── users/        # index, add, update, detail

│   ├── tasks/
│   └── ...
├── _superadmin/      # Views untuk role superadmin

├── components/       # Blade components

└── partials/
```

### Konvensi View

- Extend layout: `@extends('_admin._layout.app')`
- Set title: `@section('title', 'Page Title')`
- Konten di: `@section('content') ... @endsection`
- Gunakan `$page['title']` untuk judul halaman (dikirim dari controller)
- Data single record dicast ke object di controller sebelum ke view: `'data' => (object) $data`
- Data collection langsung pakai hasil paginate: `'data' => $data`

### Pola index.blade.php

```blade
@extends('_admin._layout.app')
@section('title', 'Judul Halaman')

@section('content')
    {{-- Header + Tombol Tambah --}}
    <div class="grid gap-3 md:flex md:justify-between md:items-center py-4">
        <h1>Data {{ $page['title'] }}</h1>
        <a href="{{ route('admin.resource.add') }}">Tambah Data</a>
    </div>

    {{-- Form Filter --}}
    <form action="{{ route('admin.resource.index') }}" method="GET" navigate-form>
        <input type="text" name="keywords" value="{{ $keywords ?? '' }}">
        <button type="submit">Cari</button>
    </form>

    {{-- Tabel Data --}}
    @forelse($data as $d)
        {{-- row --}}
    @empty
        <x-admin.empty-state />
    @endforelse

    {{-- Pagination --}}
    @if (count($data) > 0 && $data->hasPages())
        {{ $data->links() }}
    @endif
@endsection
```

### Pola Flash Message

Controller kirim via `->with('success', ...)` atau `->with('error', ...)`.
View layout menampilkan flash message secara otomatis dari layout app.

---

## Constants

### `DatabaseConst` — nama tabel

```php
// Tabel lokal: konstanta
DatabaseConst::USER           // 'users'
DatabaseConst::TASK           // 'tasks'
DatabaseConst::TASK_CATEGORY  // 'task_categories'

// Tabel cross-database: method (include prefix database)
DatabaseConst::LEMBAGA      // 'lembaga'
DatabaseConst::ROMBEL       // 'rombel'
```

### `ResponseConst` — pesan & kode HTTP

```php
ResponseConst::HTTP_SUCCESS            // 200
ResponseConst::HTTP_CREATED            // 201
ResponseConst::SUCCESS_MESSAGE_CREATED // 'Data berhasil disimpan'
ResponseConst::SUCCESS_MESSAGE_UPDATED // 'Data berhasil diperbarui'
ResponseConst::SUCCESS_MESSAGE_DELETED // 'Data berhasil dihapus'
ResponseConst::DEFAULT_ERROR_MESSAGE   // Pesan error default untuk user
```

---

## Checklist Fitur Baru

Saat membuat fitur CRUD baru, ikuti urutan ini:

1. `php artisan make:class App/Usecase/NamaUsecase` — tambah ke `DatabaseConst` jika tabel baru
2. `php artisan make:class App/Http/Controllers/Admin/NamaController`
3. Tambah route di `routes/web.php`
4. Buat views: `_admin/nama/index.blade.php`, `add.blade.php`, `update.blade.php`, `detail.blade.php`
5. Usecase: implement `getAll`, `getByID`, `create`, `update`, `delete`
6. Controller: property `$page`, `$baseRedirect`, inject Usecase via constructor

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
