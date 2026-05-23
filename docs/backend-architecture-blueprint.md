# Blueprint Backend (Laravel) — Capas SOLID

> Plan para **replicar la estructura y las buenas prácticas** de esta API en otro
> proyecto Laravel. Incluye árbol de carpetas, el flujo de una petición, las
> plantillas de cada capa, el contrato de respuesta y un checklist para agregar
> un endpoint nuevo.

---

## 1. Principios (SOLID aplicado)

| Letra | Cómo se aplica aquí |
|-------|---------------------|
| **S** Single Responsibility | `Controller` (HTTP) ≠ `Service` (negocio) ≠ `Repository` (datos) ≠ `Resource` (serialización). |
| **O** Open/Closed | Agregar un campo de salida = nuevo `Resource`/método, sin tocar el controller. |
| **L** Liskov | Mismo **envelope** `{ success, message, data }` en todos los endpoints. |
| **I** Interface Segregation | Cada endpoint devuelve **solo los campos que su consumidor usa** (un `Resource` por caso). |
| **D** Dependency Inversion | Controllers/Services dependen de **interfaces** de repositorio; el binding vive en `AppServiceProvider`. |

**Regla de oro:** nunca serializar el modelo Eloquent crudo — siempre vía un
`Resource` que expone solo lo necesario (evita filtrar `created_at`, columnas
internas, etc.).

---

## 2. Stack

- Laravel 10 · PHP 8.1+ · PostgreSQL (PDO `pgsql`).
- Validación con **Form Requests**.
- Serialización con **API Resources** (`JsonResource`).
- Errores de dominio con una **excepción propia** + render global.
- Docker (app + db) para entornos reproducibles.

---

## 3. Estructura de carpetas

```
app/
├── Http/
│   ├── Controllers/        # finos: Request → Service → Resource
│   ├── Requests/<Feature>/ # FormRequests (validación)
│   └── Resources/          # JsonResource (DTOs de salida)
├── Services/               # lógica de negocio (transacciones, reglas)
├── Repositories/
│   ├── Contracts/          # interfaces (FooRepositoryInterface)
│   └── Eloquent/           # implementaciones (EloquentFooRepository)
├── Models/                 # Eloquent
├── Exceptions/             # ApiException + Handler (render del envelope)
└── Providers/              # AppServiceProvider (bindings interfaz→impl)
routes/api.php
database/{migrations,seeders}
docker/entrypoint.sh · Dockerfile · docker-compose.yml
```

---

## 4. Flujo de una petición

```
Request HTTP
  → Route (routes/api.php)
  → FormRequest (valida)
  → Controller (orquesta, fino)
  → Service (regla de negocio, DB::transaction)
  → Repository interface → Eloquent impl (acceso a datos)
  → Resource (recorta la salida)
  → Controller envuelve en ok()/error()
Errores de dominio → ApiException → Handler → mismo envelope
```

---

## 5. Plantillas de código

### 5.1 Envelope estándar (controller base)
```php
// app/Http/Controllers/Controller.php
public function ok($message = null, $data = null) {
    $r = ['success' => true];
    if ($message) $r['message'] = $message;
    if (!is_null($data)) $r['data'] = $data;
    return response()->json($r, 200);
}
public function error($message = '', $status = 500) {
    return response()->json(['success' => false, 'message' => $message], $status);
}
```

### 5.2 Excepción de dominio + render global
```php
// app/Exceptions/ApiException.php
class ApiException extends \Exception {
    public function __construct(string $message, public readonly int $status = 400) {
        parent::__construct($message);
    }
    public static function notFound(string $m): self  { return new self($m, 404); }
    public static function forbidden(string $m): self { return new self($m, 403); }
}

// app/Exceptions/Handler.php  (dentro de register())
$this->renderable(function (ApiException $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], $e->status);
});
```

### 5.3 Repository (interfaz + Eloquent)
```php
// app/Repositories/Contracts/FooRepositoryInterface.php
interface FooRepositoryInterface {
    public function all(): \Illuminate\Support\Collection;
    public function findById(int $id): ?Foo;
    public function save(Foo $foo): void;
}

// app/Repositories/Eloquent/EloquentFooRepository.php
class EloquentFooRepository implements FooRepositoryInterface {
    private const COLUMNS = ['id', 'name'];          // ← solo lo necesario
    public function all(): Collection { return Foo::query()->select(self::COLUMNS)->orderBy('id')->get(); }
    public function findById(int $id): ?Foo { return Foo::query()->select(self::COLUMNS)->find($id); }
    public function save(Foo $foo): void { $foo->save(); }
}
```

### 5.4 Service (negocio, lanza ApiException)
```php
// app/Services/FooService.php
class FooService {
    public function __construct(private readonly FooRepositoryInterface $foos) {}

    public function list(): Collection { return $this->foos->all(); }

    public function getById(int $id): Foo {
        $foo = $this->foos->findById($id);
        if (!$foo) throw ApiException::notFound('Foo not found');
        return $foo;
    }

    public function rename(int $id, string $name): Foo {
        return DB::transaction(function () use ($id, $name) {
            $foo = $this->getById($id);
            $foo->name = $name;
            $this->foos->save($foo);
            return $foo;
        });
    }
}
```

### 5.5 Resource (salida — solo campos usados por el cliente)
```php
// app/Http/Resources/FooResource.php
class FooResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            // NADA de created_at/updated_at ni columnas internas
        ];
    }
}
```

### 5.6 Form Request (validación)
```php
// app/Http/Requests/Foo/RenameFooRequest.php
class RenameFooRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return ['id' => ['required','numeric'], 'name' => ['required','string','max:100']];
    }
}
```

### 5.7 Controller (fino)
```php
// app/Http/Controllers/FooController.php
class FooController extends Controller {
    public function __construct(private readonly FooService $service) {}

    public function index() {
        return $this->ok('Success', FooResource::collection($this->service->list()));
    }
    public function show(int $id) {
        return $this->ok('Success', new FooResource($this->service->getById($id)));
    }
    public function rename(RenameFooRequest $r) {
        $this->service->rename((int) $r->id, $r->name);
        return $this->ok('Foo updated');     // escrituras: solo confirmación
    }
}
```

### 5.8 Bindings (DIP)
```php
// app/Providers/AppServiceProvider.php → register()
$this->app->bind(FooRepositoryInterface::class, EloquentFooRepository::class);
// Services y Controllers se autoresuelven por el contenedor.
```

### 5.9 Rutas
```php
// routes/api.php
Route::middleware('api')->prefix('foo')->controller(FooController::class)->group(function () {
    Route::get('/',          'index');
    Route::get('/{id}',      'show');
    Route::post('/{id}/rename', 'rename');
});
```

---

## 6. Contrato de respuesta (consistencia = principio L)

**Éxito**
```json
{ "success": true, "message": "Success", "data": { } }
```
**Error** (con el código HTTP correcto: 400/403/404/422/500)
```json
{ "success": false, "message": "Foo not found" }
```
- Una sola clave de mensaje: `message` (no mezclar `msg`/`mensaje`).
- `snake_case` en todas las claves JSON.
- Lecturas → `GET` (id en la ruta); escrituras → `POST`/`PATCH`.
- Las escrituras devuelven solo confirmación; no reenviar el modelo completo.

---

## 7. Checklist: agregar un endpoint nuevo

1. **Migration** (+ seeder si aplica) y **Model** Eloquent (`$fillable`, `$casts`).
2. **Repository**: interfaz en `Contracts/` + impl en `Eloquent/` (con `select` de columnas necesarias).
3. **Bind** la interfaz en `AppServiceProvider`.
4. **Service**: regla de negocio, `DB::transaction` en escrituras, `ApiException` para errores.
5. **Resource**: exponer solo los campos que el cliente consume.
6. **FormRequest**: validar la entrada.
7. **Controller** fino: Request → Service → Resource → `ok()`.
8. **Ruta** en `routes/api.php`.
9. Verificar: `php artisan route:list` y resolver el controller por el contenedor.

---

## 8. Docker (entorno reproducible)

- `docker-compose.yml`: servicio **`app`** (build del Dockerfile, puerto `8000`) + **`db`** (`postgres`, healthcheck, volumen persistente).
- El servicio `app` define las variables (`DB_HOST: db`, etc.) en `environment:`.
- **`docker/entrypoint.sh`**: sincroniza esas variables al `.env`, espera a la BD,
  `php artisan config:clear`, `migrate --force`, y arranca el servidor.
- ⚠️ **Un solo `DB_HOST`**: dentro de Docker es el nombre del servicio (`db`), no
  `127.0.0.1`. El entrypoint debe reescribir el `.env` para evitar el desajuste
  con la config nativa (Laragon usa `127.0.0.1`).

---

## 9. Verificación

```bash
php artisan route:list          # rutas + métodos del controller
php -l <archivo.php>            # sintaxis
composer dump-autoload         # tras agregar clases
# resolver el controller por el contenedor (valida toda la cadena DIP):
php artisan tinker --execute="app(App\Http\Controllers\FooController::class); echo 'DI_OK';"
```
