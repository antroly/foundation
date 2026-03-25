# antroly/foundation

The core package powering the **Antroly architecture system** — a strict, minimal-ceremony approach to building Laravel applications with Actions and typed DTOs.

Every request follows a single pipeline:

```
ActionRequest → Dto → Action → Dto → Resource / ViewModel
```

Each step is mandatory — the pipeline must not be skipped.

---

## How it works

`antroly/foundation` is a **dev dependency**. It publishes architecture classes, contracts, and scaffolding into your application. Once published, **you own the code**. Modify the base classes freely to fit your application's needs.

After publishing, your application is fully independent. The Antroly package can be removed without breaking your application.

---

## Installation

**1. Require the package**

```bash
composer require antroly/foundation --dev
```

**2. Run the installer**

```bash
php artisan antroly:install
```

This publishes all base classes and architecture tests into your application. You will be prompted to publish the activity log migration.

**3. Register the service provider**

In `bootstrap/app.php`:

```php
->withProviders([
    App\Providers\AppServiceProvider::class,
])
```

This wires `ResponseMacros` (registers `response()->success()` and `response()->error()`) and binds `AppLogger` to `DatabaseLogger`.

**4. Register the exception handler**

In `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions) {
    App\Exceptions\AppExceptionHandler::register($exceptions);
})
```

This handles `ValidationException`, `DomainException`, and unexpected errors — returning a consistent JSON envelope for API requests and redirecting for web requests.

**5. Run the migration**

```bash
php artisan migrate
```

Creates the `logs` table used by `DatabaseLogger`.

---

### Swapping the logger

`AppServiceProvider` binds `AppLogger` to `DatabaseLogger` by default. To use a different implementation, rebind it in your own provider:

```php
$this->app->singleton(AppLogger::class, YourCustomLogger::class);
```

---

## What gets published

### `app/Actions/Action.php`

Base class for all use cases. Each Action represents a single application use case and owns the full flow for it.

Every Action is `final`, exposes one public method `execute()`, and is dispatched via `::run($dto)` — which resolves the Action from the container and injects constructor dependencies automatically.

**Output contract** — Actions may return only:

| Return type | When to use |
|---|---|
| `Dto` subclass | Single item — create, read, update |
| `CollectionResult` | Non-paginated list |
| `PaginatedResult` | Paginated list |
| `void` | Side-effect only — delete, dispatch, toggle |

Actions must not return arrays, Eloquent models, Eloquent collections, paginator instances, or HTTP responses. These are enforced by architecture tests.

```php
final class CreateCourseAction extends Action
{
    public function execute(CreateCourseData $dto): CourseData
    {
        $course = Course::create([
            'title' => $dto->title,
            'code'  => $dto->code,
        ]);

        return new CourseData(
            id:    $course->id,
            title: $course->title,
            code:  $course->code,
        );
    }
}

CreateCourseAction::run($dto);
```

---

### `app/Dtos/Dto.php`

Base class for all DTOs. DTOs are pure typed data containers — they carry input into Actions and output out of them.

```php
final class CreateCourseData extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly string $code,
    ) {}
}
```

The package provides one DTO abstraction: `Dto`. Naming is application-level convention — use names that describe the data, not the pipeline position:

```
CreateCourseData       — input to CreateCourseAction
CourseData             — single course output
CourseListItemData     — item in a list result
ListCoursesData        — filter/pagination input
```

DTOs must not know about `Request`, `FormRequest`, Eloquent models, or HTTP concerns.

---

### `app/Http/Requests/ActionRequest.php`

Base class for all action requests. Enforces `toDto()` on every concrete request.

```php
abstract class ActionRequest extends FormRequest
{
    abstract public function toDto(): Dto;
}
```

Every request extends `ActionRequest` and implements `toDto()` to map validated HTTP input into a typed Dto:

```php
final class CreateCourseRequest extends ActionRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code'  => ['required', 'string', 'unique:courses,code'],
        ];
    }

    public function toDto(): CreateCourseData
    {
        return new CreateCourseData(
            title: $this->validated('title'),
            code:  $this->validated('code'),
        );
    }
}
```

The Request owns the mapping. DTOs do not know about the request.

---

### `app/Exceptions/DomainException.php`

Base class for all business errors. Every domain exception carries a status code and a machine-readable error code.

```php
final class CourseExpiredException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Course expired.',
            422,
            'course.course_expired',
        );
    }
}
```

Caught automatically by `AppExceptionHandler` — returns a structured JSON error or redirects for web requests.

---

### `app/Exceptions/AppExceptionHandler.php`

Modern Laravel 11/12 exception handler using `withExceptions()`. Handles `ValidationException`, `DomainException`, and unexpected errors consistently for both API and Blade responses.

---

### `app/Http/Controllers/BaseController.php`

Provides `toApiError()`, `handleException()`, and `buildRuleBasedErrorBags()` used by the exception handler. Extend this in your own controllers.

---

### `app/Http/Resources/BaseResource.php`

API responses extend `BaseResource`. Resources must receive DTOs, never Eloquent models.

```php
final class CourseResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->resource->id,
            'title' => $this->resource->title,
        ];
    }
}
```

---

### `app/Http/ViewModels/BaseViewModel.php`

For Blade applications. ViewModels convert DTOs into view data via `$this->data`.

```php
final class CourseViewModel extends BaseViewModel
{
    public function toArray(): array
    {
        return [
            'title' => $this->data->title,
        ];
    }
}
```

---

### `app/Http/Macros/ResponseMacros.php`

Registers `response()->success()` and `response()->error()` with a consistent JSON envelope:

```php
response()->success(200, $data);
response()->success(201, $data, 'Course created.');
response()->error(422, 'Validation failed.', $errorBags, 'validation.failed');
response()->error(404);
```

Both return the same envelope structure:

```json
{
    "statusCode": 200,
    "error": false,
    "message": null,
    "errorCode": null,
    "errorBags": null,
    "data": {}
}
```

---

### `app/Dtos/Common/CollectionResult.php` and `app/Dtos/Common/PaginatedResult.php`

Output wrappers for list and paginated results. Actions must use these instead of returning raw arrays or paginators.

```php
// Collection
return new CollectionResult(
    items: $courses->map(fn($c) => new CourseListItemData(...))->all(),
);

// Paginated
return PaginatedResult::fromPaginator(
    paginator: Course::query()->paginate($dto->perPage),
    mapper: fn($course) => new CourseListItemData(
        id:    $course->id,
        title: $course->title,
    ),
);
```

---

### `app/Logging/Contracts/AppLogger.php`

Contract for application logging. Inject this wherever logging is needed — never depend on the concrete logger or Laravel's `Log` facade directly.

---

### `app/Logging/DatabaseLogger.php`

Default implementation writing to the `logs` database table. Bound to `AppLogger` in `AppServiceProvider`. Swap it by rebinding in your own provider.

---

### `app/Models/ActivityLog.php`

Eloquent model for the `logs` table. Immutable — no `updated_at`.

---

### `app/Providers/AppServiceProvider.php`

Wires `ResponseMacros` and binds `AppLogger` to `DatabaseLogger`. Register this in `bootstrap/app.php`.

---

## Scaffolding commands

### `make:action`

Generates an Action and its test. Domain prefix is optional:

```bash
# Without domain
php artisan make:action CreateCourse

# With domain
php artisan make:action Course/CreateCourse
```

Generates:
```
app/Actions/CreateCourseAction.php
tests/Unit/Actions/CreateCourseActionTest.php

app/Actions/Course/CreateCourseAction.php
tests/Unit/Actions/Course/CreateCourseActionTest.php
```

---

### `make:action-dto`

Generates a Dto class:

```bash
php artisan make:action-dto Course/CreateCourseData
php artisan make:action-dto Course/CourseData
```

Generates:
```
app/Dtos/Course/CreateCourseData.php
app/Dtos/Course/CourseData.php
```

Naming is application-level convention. The package does not enforce input/output subtype naming — use names that describe the data.

---

### `make:action-request`

Generates an `ActionRequest` with `toDto()`:

```bash
php artisan make:action-request Course/CreateCourse
```

Generates:
```
app/Http/Requests/Course/CreateCourseRequest.php
```

The generated request extends `ActionRequest` and maps validated input into a Dto via `toDto()`.

---

### `make:action-resource`

Generates an API `Resource` (default) or a `ViewModel` for Blade:

```bash
# API Resource (default)
php artisan make:action-resource Course/CreateCourse

# ViewModel for Blade
php artisan make:action-resource Course/CreateCourse --type=web
```

| Flag | Output |
|------|--------|
| `--type=api` (default) | `CreateCourseResource` extending `BaseResource` |
| `--type=web` | `CreateCourseViewModel` extending `BaseViewModel` |

---

### `make:domain-exception`

Generates a domain exception with a derived message and error code:

```bash
php artisan make:domain-exception Course/CourseExpired
```

Generates:
```
app/Exceptions/Course/CourseExpiredException.php
```

---

## Scaffolding workflow

Run these commands in sequence to scaffold a complete feature:

```bash
php artisan make:action Course/CreateCourse
php artisan make:action-dto Course/CreateCourseData
php artisan make:action-dto Course/CourseData
php artisan make:action-request Course/CreateCourse
php artisan make:action-resource Course/CreateCourse --type=api
```

This generates the full pipeline — Action, both DTOs, ActionRequest, and API Resource — ready for your business logic.

---

## Example flow

### Create — single item

```php
// Controller
final class CourseController extends BaseController
{
    public function store(CreateCourseRequest $request): JsonResponse
    {
        $result = CreateCourseAction::run($request->toDto());

        return response()->success(201, new CourseResource($result));
    }
}

// Request — validates and maps to Dto
final class CreateCourseRequest extends ActionRequest
{
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255']];
    }

    public function toDto(): CreateCourseData
    {
        return new CreateCourseData(title: $this->validated('title'));
    }
}

// Action
final class CreateCourseAction extends Action
{
    public function execute(CreateCourseData $dto): CourseData
    {
        $course = Course::create(['title' => $dto->title]);

        return new CourseData(id: $course->id, title: $course->title);
    }
}

// Resource
final class CourseResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource->id, 'title' => $this->resource->title];
    }
}
```

---

### List — paginated results

```php
// Controller
final class CourseController extends BaseController
{
    public function index(ListCoursesRequest $request): JsonResponse
    {
        $result = ListCoursesAction::run($request->toDto());

        return response()->success(200, [
            'items'       => CourseListItemResource::collection($result->items),
            'total'       => $result->total,
            'perPage'     => $result->perPage,
            'currentPage' => $result->currentPage,
            'lastPage'    => $result->lastPage,
        ]);
    }
}

// Action — maps models to DTOs inside the Action, wraps pagination metadata
final class ListCoursesAction extends Action
{
    public function execute(ListCoursesData $dto): PaginatedResult
    {
        return PaginatedResult::fromPaginator(
            paginator: Course::query()
                ->with('instructor')
                ->paginate($dto->perPage),
            mapper: fn($course) => new CourseListItemData(
                id:         $course->id,
                title:      $course->title,
                instructor: new InstructorData(name: $course->instructor->name),
            ),
        );
    }
}
```

No paginator reaches the controller. No database query runs in the Resource.

---

## Architecture tests

Publishing `antroly-tests` adds `tests/Architecture/ArchitectureTest.php` to your project. Run it as part of your normal Pest suite:

```bash
./vendor/bin/pest
```

### Rules enforced by arch tests

| Rule | Enforcement |
|------|-------------|
| Actions extend `Action`, are `final` | Arch test |
| Actions must not depend on `Request` / `FormRequest` | Arch test |
| Actions must not return Eloquent models or collections | Arch test |
| Actions must not return paginator instances | Arch test |
| Actions must not return HTTP responses or `Responsable` | Arch test |
| DTOs extend `Dto`, are `final` | Arch test |
| DTOs must not depend on Eloquent | Arch test |
| DTOs must not depend on `Request` / `FormRequest` | Arch test |
| Requests extend `ActionRequest`, are `final` | Arch test |
| Resources extend `BaseResource`, are `final`, no Eloquent | Arch test |
| ViewModels extend `BaseViewModel`, are `final`, no Eloquent | Arch test |
| Controllers extend `BaseController`, are `final`, no Eloquent | Arch test |
| Domain exceptions extend `DomainException`, are `final` | Arch test |

### Rules that remain convention-based

| Rule | Reason |
|------|--------|
| Actions must not call other Actions | Method-level; not structurally enforceable |
| DTOs must be readonly (no mutable state) | Property-level; not in current arch tooling |
| Resources must receive DTOs, not models | Type-level; not statically checkable |
| Controllers must call `$request->toDto()` and not reconstruct DTOs manually | Not statically checkable |

See [RULEBOOK.md](RULEBOOK.md) for the full architectural guidelines.

---

## What Antroly does not include

Intentionally absent: repository layers, generic service interfaces, command buses, DTO auto-hydration frameworks, complex transformer layers. Actions interact directly with Eloquent. That is sufficient.

---

## Contributing

```bash
./vendor/bin/pest   # run tests
composer lint       # pint code style
composer analyse    # phpstan static analysis
```

---

## License

MIT
