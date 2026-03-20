# antroly/foundation

The core package powering the **Antroly architecture system** — a strict, minimal-ceremony approach to building Laravel applications with Actions and typed DTOs.

Every request follows a single pipeline:

```
FormRequest → SubmitDto → Action → ResultDto → Resource / ViewModel
```

---

## How it works

`antroly/foundation` is a **dev dependency**. It does not ship runtime classes into your application — instead, it publishes them directly into your project.

Once published, **you own the code**. Modify the base classes freely to fit your application's needs. The package remains as a dev dependency only to provide scaffolding commands and architecture testing helpers.

---

## Installation

```bash
composer require antroly/foundation --dev
```

Publish the base classes:

```bash
php artisan vendor:publish --tag=antroly-foundation
```

Publish the migration:

```bash
php artisan vendor:publish --tag=antroly-migrations
php artisan migrate
```

Publish the architecture test:

```bash
php artisan vendor:publish --tag=antroly-tests
```

Register in `bootstrap/app.php`:

```php
use App\Exceptions\AppExceptionHandler;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Configuration\Exceptions;

->withProviders([
    AppServiceProvider::class,
])
->withExceptions(function (Exceptions $exceptions) {
    AppExceptionHandler::register($exceptions);
})
```

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

### `app/Dtos/BaseDto.php`

Base class for Submit DTOs and Result DTOs. DTOs hold typed data only — no HTTP logic, no persistence.

```php
final class CreateCourseSubmitDto extends BaseDto
{
    public function __construct(
        public readonly string $title,
        public readonly string $code,
    ) {}
}
```

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

API responses extend `BaseResource`. Resources receive Result DTOs, not Eloquent models.

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

For Blade applications. ViewModels convert Result DTOs into view data.

```php
final class CourseViewModel extends BaseViewModel
{
    public function toArray(): array
    {
        return [
            'title' => $this->dto->title,
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

### `app/Logging/Contracts/ActivityLoggerInterface.php`

Contract for activity logging. Inject this wherever logging is needed — never depend on a concrete logger directly.

---

### `app/Logging/ActivityLogger.php`

Default implementation writing to the `logs` database table. Bound to `ActivityLoggerInterface` in `AppServiceProvider`. Swap it by rebinding in your own provider.

---

### `app/Models/ActivityLog.php`

Eloquent model for the `logs` table. Immutable — no `updated_at`.

---

### `app/Providers/AppServiceProvider.php`

Wires `ResponseMacros` and binds `ActivityLoggerInterface` to `ActivityLogger`. Register this in `bootstrap/app.php`.

---

## Scaffolding commands

### `make:action`

Generates an Action with a Submit DTO and a Result DTO, grouped by domain:

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

### `make:antroly-request`

Generates a `FormRequest` with a `toDto()` method pre-wired to the Submit DTO:

```bash
php artisan make:antroly-request Course/CreateCourseRequest
```

Generates:
```
app/Http/Requests/Course/CreateCourseRequest.php
```

---

### `make:antroly-resource`

Generates an API `Resource` (default) or a `ViewModel` for Blade (`--web`):

```bash
php artisan make:antroly-resource Course/CourseResource
php artisan make:antroly-resource Course/CourseViewModel --web
```

Generates:
```
app/Http/Resources/Course/CourseResource.php
app/Http/ViewModels/Course/CourseViewModel.php
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

## Architecture tests

Publishing `antroly-tests` adds `tests/ArchitectureTest.php` to your project. Run it as part of your normal Pest suite:

```bash
./vendor/bin/pest
```

Enforces:
- Actions extend `Action` and do not return Eloquent models
- DTOs extend `BaseDto` and do not depend on Eloquent
- Resources extend `BaseResource` and do not depend on Eloquent
- Controllers do not use Eloquent directly
- ViewModels extend `BaseViewModel`

See [RULEBOOK.md](RULEBOOK.md) for the full architectural guidelines.

---

## What Antroly does not include

Intentionally absent: repository layers, generic service interfaces, command buses, DTO auto-hydration frameworks, complex transformer layers. Actions interact directly with Eloquent. That's enough.

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