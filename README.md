# antroly/foundation

The core package powering the **Antroly architecture system** — a strict, minimal-ceremony approach to building Laravel applications with Actions and typed DTOs.

Every request follows a single pipeline:

```
FormRequest → SubmitDto → Action → ResultDto → Resource / ViewModel
```

---

## How it works

`antroly/foundation` is a **dev dependency**. It does not ship runtime classes into your application — instead, it publishes them.

Running:

```bash
php artisan vendor:publish --provider="Antroly\Foundation\FoundationServiceProvider"
```

copies the base classes directly into your project:

| Class | Published to |
|---|---|
| `BaseDto` | `app/Dtos/` |
| `Action` | `app/Actions/` |
| `BaseResource` | `app/Http/Resources/` |
| `BaseViewModel` | `app/Http/ViewModels/` |
| `DomainException` and variants | `app/Exceptions/` |
| `ActivityLogger` contract | `app/Logging/Contracts/` |

From that point, **you own the code**. Modify the base classes freely to fit your application's needs.

The package remains as a dev dependency to provide scaffolding commands and architecture testing helpers.

---

## Philosophy

Antroly enforces three principles:

**One place for everything.** Controllers don't hold business logic. Requests don't hold domain logic. Models don't hold application workflows. Use cases live in Actions — full stop.

**No unnecessary abstractions.** Laravel already gives you strong primitives. Antroly builds on them instead of wrapping them. No repository layers, no service explosion, no command buses, no DTO hydration frameworks.

**Predictability over flexibility.** Every use case looks the same. A developer new to the codebase should always know where to look.

---

## What gets published

### DTO — `App\Dtos\BaseDto`

Typed base class for Submit DTOs and Result DTOs. DTOs hold data only — no HTTP logic, no persistence.

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

### Action — `App\Actions\Action`

All application use cases extend `Action`. Actions may use Eloquent models internally but always return a Result DTO — never a model.

```php
final class CreateCourseAction extends Action
{
    public function execute(CreateCourseSubmitDto $dto): CourseResultDto
    {
        $course = Course::create([
            'title' => $dto->title,
            'code'  => $dto->code,
        ]);

        return new CourseResultDto(
            id:    $course->id,
            title: $course->title,
            code:  $course->code,
        );
    }
}
```

---

### Domain Exceptions — `App\Exceptions`

A base exception hierarchy for business errors — distinct from HTTP validation errors.

| Class | Purpose |
|---|---|
| `DomainException` | Base class |
| `ValidationException` | Failed business rule |
| `GuardViolationException` | Guard check failed |
| `NotFoundException` | Resource not found |

---

### Resource — `App\Http\Resources\BaseResource`

API responses extend `BaseResource`. Resources receive Result DTOs, not Eloquent models.

```php
final class CourseResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->resource->id,
            'title' => $this->resource->title,
        ];
    }
}
```

---

### ViewModel — `App\Http\ViewModels\BaseViewModel`

For Blade applications. ViewModels convert Result DTOs into view data, keeping presentation logic out of controllers.

---

### Response Macros

Minimal response helpers registered automatically:

```php
response()->success($data);
response()->error($message);
```

---

### Logging Abstraction — `App\Logging\Contracts\ActivityLogger`

A thin contract for activity logging. Keeps domain logic decoupled from any specific logging driver or external service.

---

## What stays in the package (dev only)

These are not published — they run from the package directly during development:

**Architecture Testing Helpers** — Pest-compatible utilities to enforce structural boundaries in CI:

```php
arch()->expect('App\Actions')->toReturnDtos();
arch()->expect('App\Http\Resources')->notToDependOn('Illuminate\Database\Eloquent\Model');
```

**Scaffolding commands** — `make:domain` and related stubs that generate boilerplate domain structure.

---

## What Antroly does not include

Intentionally absent:

- Repository layers
- Generic service interfaces
- Command buses
- DTO auto-hydration frameworks
- Complex transformer layers

Actions interact directly with Eloquent. That's enough.

---

## The Antroly ecosystem

| Package | Role |
|---|---|
| `antroly/foundation` | Publishable base classes + dev tooling — this package |
| `antroly/starter` | Laravel starter kit with foundation pre-installed and configured |

Start a new project with:

```bash
composer create-project antroly/starter my-project
```

Scaffold a domain:

```bash
php artisan make:domain Course
```

The starter includes Laravel, this package, Pest, Pint, PHPStan, and a CI workflow preconfigured.

---

## Contributing

Before submitting a pull request:

- Run `pint` for code style
- Run `phpstan` for static analysis
- Ensure all tests pass

---

## License

MIT