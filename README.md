# antroly/foundation

The core package powering the **Antroly architecture system** — a strict, minimal-ceremony approach to building Laravel applications with Actions and typed DTOs.

Every request follows a single pipeline:

```
FormRequest → SubmitDto → Action → ResultDto → Resource / ViewModel
```

Each step is mandatory — the pipeline must not be skipped.

---

## How it works

`antroly/foundation` is a **dev dependency**. It publishes architecture classes, contracts, and scaffolding into your application. Once published, **you own the code**. Modify the base classes freely to fit your application's needs.

After publishing, your application is fully independent. The Antroly package can be removed without breaking your application.

---

## Installation

```bash
composer require antroly/foundation --dev
php artisan antroly:install
```

The installer publishes all base classes, wires the service provider, and optionally publishes the migration. Follow the printed next steps to register `AppServiceProvider` and `AppExceptionHandler` in `bootstrap/app.php`.

---

## What gets published

### `app/Actions/Action.php`

Base class for all use cases. Resolved via the container — constructor dependencies are injected automatically.

```php
final class CreateCourseAction extends Action
{
    public function execute(CreateCourseSubmitDto $dto): CreateCourseResultDto
    {
        $course = Course::create([
            'title' => $dto->title,
            'code'  => $dto->code,
        ]);

        return new CreateCourseResultDto(
            id:    $course->id,
            title: $course->title,
            code:  $course->code,
        );
    }
}

// Dispatch via static helper
CreateCourseAction::run($dto);
```

---

### DTOs

DTOs are plain `final` classes with no base class. They carry typed input into Actions and typed output out of them.

**SubmitDto** — implements `App\Contracts\Dto\FromRequest` (optional), carries validated HTTP input to the Action:

```php
final class CreateCourseSubmitDto implements FromRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $code,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        return new static(
            title: $request->validated('title'),
            code:  $request->validated('code'),
        );
    }
}
```

**ResultDto** — implements `App\Contracts\Dto\ResultData` (optional), carries typed output from the Action:

```php
final class CreateCourseResultDto implements ResultData
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
        public readonly string $code,
    ) {}
}
```

These interfaces are optional and used when that behavior is needed.

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

API responses extend `BaseResource`. Resources must receive Result DTOs, never Eloquent models.

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

For Blade applications. ViewModels convert Result DTOs into view data via `$this->data`.

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

Generates an Action with a SubmitDto and a ResultDto, grouped by domain:

```bash
php artisan make:action Course/CreateCourse
```

Generates:
```
app/Actions/Course/CreateCourseAction.php
app/Dtos/Course/CreateCourseSubmitDto.php
app/Dtos/Course/CreateCourseResultDto.php
```

---

### `make:action-request`

Generates a `FormRequest` with a `toDto()` method. Supports two mapping strategies:

```bash
# Request maps to DTO (default)
php artisan make:action-request Course/CreateCourse

# DTO maps itself via fromRequest()
php artisan make:action-request Course/CreateCourse --mapping=dto
```

| Flag | Behavior |
|------|----------|
| `--mapping=request` (default) | Request constructs the DTO inline via `new SubmitDto(...)` |
| `--mapping=dto` | Request delegates to `SubmitDto::fromRequest($this)` |

Generates:
```
app/Http/Requests/Course/CreateCourseRequest.php
```

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

Generates:
```
app/Http/Resources/Course/CreateCourseResource.php
app/Http/ViewModels/Course/CreateCourseViewModel.php
```

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

## Example flow

A complete request cycle for a JSON API endpoint:

```php
// 1. Controller — thin HTTP adapter
final class CourseController extends BaseController
{
    public function store(CreateCourseRequest $request): JsonResponse
    {
        $result = CreateCourseAction::run($request->toDto());

        return response()->success(201, new CourseResource($result));
    }
}

// 2. Request — validates and maps to SubmitDto
final class CreateCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255']];
    }

    public function toDto(): CreateCourseSubmitDto
    {
        return new CreateCourseSubmitDto(title: $this->validated('title'));
    }
}

// 3. Action — business logic only
final class CreateCourseAction extends Action
{
    public function execute(CreateCourseSubmitDto $dto): CreateCourseResultDto
    {
        $course = Course::create(['title' => $dto->title]);

        return new CreateCourseResultDto(id: $course->id, title: $course->title);
    }
}

// 4. Resource — formats ResultDto as JSON
final class CourseResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource->id, 'title' => $this->resource->title];
    }
}
```

---

## Architecture tests

Publishing `antroly-tests` adds `tests/Architecture/ArchitectureTest.php` to your project. Run it as part of your normal Pest suite:

```bash
./vendor/bin/pest
```

Enforces:
- Actions extend `Action` and do not return Eloquent models
- DTOs are `final` and do not depend on Eloquent
- Resources extend `BaseResource` and do not depend on Eloquent
- Controllers do not use Eloquent directly
- ViewModels extend `BaseViewModel`
- Domain exceptions extend `DomainException` and are `final`

See [RULEBOOK.md](RULEBOOK.md) for the full architectural guidelines.

---

## What Antroly does not include

Intentionally absent: repository layers, generic service interfaces, command buses, DTO auto-hydration frameworks, complex transformer layers. Actions interact directly with Eloquent. That is sufficient.

---

## Contributing

```bash
composer pest      # run tests
composer lint      # pint code style
composer analyse   # phpstan static analysis
```

---

## License

MIT
