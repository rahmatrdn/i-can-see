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

