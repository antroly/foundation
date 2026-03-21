# Antroly Rulebook

This document defines the architectural rules for applications built with the Antroly system. When in doubt about where code belongs, consult this file.

---

## The pipeline

Every request in an Antroly application follows one pipeline, without exception:

```
FormRequest → SubmitDto → Action → ResultDto → Resource / ViewModel
```

Each step is mandatory — the pipeline must not be skipped.

---

## Actions

Actions represent a single application use case.

They are the primary boundary where business logic lives.

### Structure

An Action must:
- extend `App\Actions\Action`
- be `final`
- implement exactly one public method: `execute()`
- accept exactly one typed Submit DTO
- return a typed Result DTO, `CollectionResult`, or `PaginatedResult`

### An Action may:
- query and persist Eloquent models
- eager load required relations
- define transaction boundaries
- call Guards, Validators, calculators, or domain helpers/services
- inject dependencies via the constructor
- throw Domain Exceptions or Validation Exceptions
- map loaded state into Result DTOs, `CollectionResult`s, or `PaginatedResult`s

### An Action must:
- keep the use-case flow explicit
- load all required data before mapping output
- return DTO-based output only

### An Action must not:
- accept a `Request` or `FormRequest`
- return HTTP responses, redirects, or call HTTP helpers
- contain presentation formatting
- call other Actions
- rely on lazy loading during DTO mapping

### Delegation

Actions may be large when the use case is large. Size alone is not a design problem.

An Action may delegate focused sub-responsibilities when that improves clarity, reuse, testability, or isolates independently complex logic.

Delegation does not change ownership: the Action still owns the use case.

### Dispatching

```php
CreateCourseAction::run($dto);
```

The static `run()` helper resolves the Action from the container and calls `execute()` internally.

---

## DTOs

DTOs are plain data containers. They carry typed input into Actions and typed output out of them.

There is no base DTO class. DTOs are plain `final` classes. Two optional contracts are available:

- `App\Contracts\Dto\FromRequest` — implemented by SubmitDtos that map themselves from a FormRequest
- `App\Contracts\Dto\ResultData` — marker interface implemented by ResultDtos

These interfaces are optional and used when that behavior is needed.

**A DTO must:**
- Be `final`
- Use typed, `readonly` constructor properties

**A DTO must not:**
- Contain business logic
- Depend on Eloquent models
- Contain HTTP logic
- Have setters or mutable state

**Naming convention:**
- Input: `{Action}SubmitDto` — e.g. `CreateCourseSubmitDto`
- Output: `{Domain}ResultDto` — e.g. `CourseResultDto`

```php
// correct — SubmitDto with fromRequest() mapping
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

// correct — ResultDto as a plain final class
final class CreateCourseResultDto implements ResultData
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
    ) {}
}

// wrong — business logic in a DTO
final class CreateCourseSubmitDto implements FromRequest
{
    public function isValid(): bool
    {
        return strlen($this->title) > 3;
    }
}
```

---

## List and Pagination Output

Actions must use typed output wrappers for list and paginated results. Raw arrays and bare paginators must not leave the Action boundary.

| Result type | Return type |
|---|---|
| Single item | `ResultDto` |
| Unordered/filtered list | `CollectionResult<ItemDto>` |
| Paginated list | `PaginatedResult<ItemDto>` |

### CollectionResult

Wraps a list of already-mapped Result DTOs.

```php
return new CollectionResult(
    items: $courses->map(fn($c) => new CourseItemDto(
        id:    $c->id,
        title: $c->title,
    ))->all(),
);
```

### PaginatedResult

Wraps pagination metadata and a list of already-mapped Result DTOs.

Use the `fromPaginator()` factory — it maps models → DTOs and extracts pagination metadata in one step:

```php
return PaginatedResult::fromPaginator(
    paginator: Course::query()->paginate($dto->perPage),
    mapper: fn($course) => new CourseItemDto(
        id:    $course->id,
        title: $course->title,
    ),
);
```

### Rules

**An Action must not:**
- return a raw `array` for a list result
- return a `LengthAwarePaginator` or any paginator instance directly
- perform DTO mapping inside a Resource or ViewModel

**An Action must:**
- map models → DTOs before constructing the wrapper
- ensure DTO construction does not trigger lazy loading

The arch test enforces the paginator leakage rule automatically.

---

## Controllers

Controllers are thin HTTP adapters. Their only job is to receive a request, delegate to an Action, and return a response.

**A controller must:**
- Extend `App\Http\Controllers\BaseController`
- Delegate all business logic to an Action
- Return a response immediately after the Action

**A controller may:**
- Use a `FormRequest` for validation
- Use a `Resource` or `ViewModel` to format the response
- Inject dependencies via the constructor

**A controller must not:**
- Contain business logic
- Query the database directly
- Use Eloquent models directly
- Call multiple Actions in sequence to compose a result

```php
// correct
final class CourseController extends BaseController
{
    public function store(CreateCourseRequest $request): JsonResponse
    {
        $result = CreateCourseAction::run($request->toDto());

        return response()->success(201, new CourseResource($result));
    }
}

// wrong — business logic in a controller
final class CourseController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        $course = Course::create($request->validated());
        $course->notify();
        Cache::forget('courses');

        return response()->json($course);
    }
}
```

---

## FormRequests

FormRequests handle HTTP validation only. They are the boundary between raw HTTP input and typed application input.

**A FormRequest must:**
- Extend `Illuminate\Foundation\Http\FormRequest`
- Implement `rules()` for validation
- Implement `toDto()` to produce a Submit DTO

**A FormRequest must not:**
- Contain business logic
- Query the database (except for existence validation rules)
- Know anything about what the Action does with the data

Two valid mapping strategies for `toDto()`:

```php
// request mapping — Request constructs the DTO inline
final class CreateCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code'  => ['required', 'string', 'unique:courses,code'],
        ];
    }

    public function toDto(): CreateCourseSubmitDto
    {
        return new CreateCourseSubmitDto(
            title: $this->validated('title'),
            code:  $this->validated('code'),
        );
    }
}

// dto mapping — DTO is responsible for mapping itself
final class CreateCourseRequest extends FormRequest
{
    public function rules(): array { /* ... */ }

    public function toDto(): CreateCourseSubmitDto
    {
        return CreateCourseSubmitDto::fromRequest($this);
    }
}
```

Use `make:action-request --mapping=request` (default) or `--mapping=dto` to generate either form.

---

## Validation

Antroly separates validation into two distinct layers that must not be mixed.

### Transport validation — FormRequest

The FormRequest validates that the incoming HTTP input is structurally correct and safe to process. This layer runs before the Action.

**Allowed in FormRequest:**
- field presence, type, format, and length rules
- uniqueness and existence checks against the database
- authorization via `authorize()`

**Not allowed in FormRequest:**
- business rules (those belong in the Action)

### Business validation — Action

Business rules are enforced inside the Action. When a rule is violated, the Action throws a `DomainException`.

```php
// wrong — business rule in FormRequest
public function rules(): array
{
    return [
        'course_id' => ['required', Rule::exists('courses')->where('status', 'active')],
        // checking enrollment capacity here is wrong — that is a business rule
    ];
}

// correct — business rule in Action
public function execute(EnrollStudentSubmitDto $dto): EnrollStudentResultDto
{
    $course = Course::query()->findOrFail($dto->courseId);

    if ($course->isFull()) {
        throw new CourseFullException();
    }

    // ...
}
```

### Extracted validators and guards

Business validation may be extracted into dedicated `Guard` or `Validator` classes when it is complex enough to warrant isolation. This is not required — simple checks may live inline in the Action.

```php
// allowed — extracted guard
final class EnrollmentCapacityGuard
{
    public function ensure(Course $course): void
    {
        if ($course->isFull()) {
            throw new CourseFullException();
        }
    }
}
```

The Action remains the orchestrator and owner of the use case regardless of extraction.

---

## Resources and ViewModels

Resources and ViewModels are presentation adapters. They transform a Result DTO into a format suitable for the response.

**A Resource must:**
- Extend `App\Http\Resources\BaseResource`
- Receive a Result DTO via `$this->resource`, never an Eloquent model
- Be used for API (JSON) responses

**A ViewModel must:**
- Extend `App\Http\ViewModels\BaseViewModel`
- Access the Result DTO via `$this->data`
- Implement `toArray()` to return view data
- Be used for Blade responses

**Both must not:**
- Depend on Eloquent models
- Contain business logic
- Make database queries

```php
// correct — API Resource
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

// wrong — database query inside a Resource
final class CourseResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_count' => $this->resource->students()->count(), // not allowed
        ];
    }
}
```

Use `make:action-resource` for an API Resource or `make:action-resource --type=web` for a ViewModel.

---

## Data loading

All data required by an Action must be loaded inside the Action before DTO mapping begins. DTO construction must not trigger database queries.

### Eager loading

Load all required relations explicitly. Do not rely on Laravel's lazy loading.

```php
// correct
$course = Course::query()
    ->with(['instructor', 'enrollments'])
    ->findOrFail($dto->courseId);

return new CourseResultDto(
    id:          $course->id,
    instructor:  new InstructorDto(name: $course->instructor->name),
    enrollment:  $course->enrollments->count(),
);

// wrong — lazy loading triggered during DTO mapping
$course = Course::query()->findOrFail($dto->courseId);

return new CourseResultDto(
    instructor: new InstructorDto(name: $course->instructor->name), // N+1
);
```

### Model::preventLazyLoading()

Enable lazy loading prevention in non-production environments to catch violations at development time:

```php
// AppServiceProvider
Model::preventLazyLoading(! app()->isProduction());
```

### Rules

**An Action must:**
- eager load all relations required for DTO mapping
- complete all data loading before constructing any DTO

**Resources and ViewModels must not:**
- access Eloquent relations
- trigger any database queries (enforced by the arch test)

**DTOs must not:**
- contain Eloquent models as properties
- perform queries in constructors or factory methods

---

## Domain Exceptions

Domain Exceptions represent business rule violations — things that are expected to go wrong in normal application flow.

**A Domain Exception must:**
- Extend `App\Exceptions\DomainException`
- Be `final`
- Define its own message, status code, and error code in the constructor
- Use a human-readable, hardcoded message string
- Use a dot-notation error code: `domain.reason` — e.g. `course.course_expired`

**A Domain Exception must not:**
- Extend `\Exception` or `\RuntimeException` directly
- Use translation keys (keep messages simple and hardcoded)
- Contain business logic

**Status code conventions:**
- `422` — business rule violation (default)
- `404` — resource not found
- `403` — forbidden by policy
- `409` — conflict with current state

```php
// correct
final class CourseExpiredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Course expired.', 422, 'course.course_expired');
    }
}

// wrong — extending base Exception
final class CourseExpiredException extends \Exception
{
    // ...
}
```

---

## Logging

Logging must always go through the `AppLogger` contract. Never use Laravel's `Log` facade or `logger()` helper directly in business code.

**The logger may be used in:**
- Actions (for significant business events)
- The exception handler (automatic — already wired)

**The logger must not be used in:**
- DTOs
- Resources or ViewModels
- Controllers (use the exception handler instead)

```php
// correct
final class EnrollStudentAction extends Action
{
    public function __construct(
        private readonly AppLogger $logger,
    ) {}

    public function execute(EnrollStudentSubmitDto $dto): EnrollmentResultDto
    {
        // ...
        $this->logger->info('Student enrolled', ['student_id' => $dto->studentId]);
    }
}

// wrong — using the Log facade directly
use Illuminate\Support\Facades\Log;

final class EnrollStudentAction extends Action
{
    public function execute(EnrollStudentSubmitDto $dto): EnrollmentResultDto
    {
        Log::info('Student enrolled');
    }
}
```

The default implementation is `DatabaseLogger`, bound to `AppLogger` in `AppServiceProvider`. Swap it by rebinding in your own provider.

---

## Non-HTTP entry points

Actions are not HTTP-only. Jobs, listeners, console commands, and scheduled tasks must also delegate business logic to Actions.

The `FromRequest` contract is exclusively for HTTP-boundary DTOs. Non-HTTP callers construct SubmitDtos directly from their own data.

```php
// Job — constructs SubmitDto from job payload
final class ProcessEnrollmentJob implements ShouldQueue
{
    public function __construct(
        private readonly int $courseId,
        private readonly int $studentId,
    ) {}

    public function handle(): void
    {
        $dto = new EnrollStudentSubmitDto(
            courseId:  $this->courseId,
            studentId: $this->studentId,
        );

        EnrollStudentAction::run($dto);
    }
}

// Console command — constructs SubmitDto from input arguments
final class ExpireCoursesCommand extends Command
{
    public function handle(): void
    {
        $dto = new ExpireCoursesSubmitDto(
            before: now()->subMonths(6),
        );

        ExpireCoursesAction::run($dto);
    }
}
```

**Rules:**

- Jobs, listeners, and commands must not contain business logic — delegate to Actions
- `FromRequest` must not be implemented by DTOs used outside HTTP contexts
- Actions must not know or care about whether the caller is HTTP, a job, a command, or a listener

---

## Anti-patterns

These patterns are explicitly forbidden in Antroly applications.

**Fat controllers** — controllers that contain queries, conditions, or business logic. All logic belongs in Actions.

**Models as return values** — Actions must return Result DTOs. Returning Eloquent models leaks persistence concerns into the presentation layer.

**Logic in DTOs** — DTOs are data containers. Validation, transformation, and computation belong in Actions or FormRequests.

**Direct Eloquent in Resources** — Resources must receive a Result DTO. Querying the database inside a Resource creates N+1 problems and breaks the pipeline.

**Bypassing the pipeline** — calling Eloquent directly in a controller, or calling a FormRequest from an Action, breaks the separation of concerns the architecture depends on.

**Generic service classes** — `CourseService` with ten methods is a fat controller in disguise. Each use case gets its own Action.

**Repository layers** — Laravel's Eloquent is already a repository. Adding another abstraction on top adds complexity without value.

**Catching exceptions in Actions** — Actions should throw, not catch. Exception handling belongs in the exception handler, not in business logic.

---

## Hard rules vs preferred defaults

Some rules in Antroly are absolute and enforced by arch tests or code constraints. Others are strong defaults that may be adjusted in specific circumstances.

### Hard rules — never break these

| Rule | Enforcement |
|---|---|
| Actions extend `App\Actions\Action` | Arch test |
| Actions are `final` | Arch test |
| Actions must not return Eloquent models | Arch test |
| Actions must not return paginators directly | Arch test |
| DTOs are `final` | Arch test |
| DTOs must not depend on Eloquent | Arch test |
| Resources must not query the database | Arch test |
| ViewModels must not query the database | Arch test |
| Controllers must not query the database | Arch test |
| Domain exceptions extend `DomainException` and are `final` | Arch test |
| Actions must not call other Actions | Convention |
| Actions must not accept `Request` / `FormRequest` | Convention |
| Actions must not return HTTP responses | Convention |

### Preferred defaults — strong but adjustable

| Default | When to deviate |
|---|---|
| Pipeline SubmitDtos implement `FromRequest` | Non-HTTP entry points construct DTOs directly — `FromRequest` is HTTP-only |
| Pipeline ResultDtos implement `ResultData` | Acceptable to skip for simple internal DTOs not passing through Resources |
| `Action::run($dto)` as the dispatch path | Direct `app(Action::class)->execute($dto)` is valid but less idiomatic |
| Guards / Validators as extracted classes | Simple business checks may live inline in the Action |
| `Model::preventLazyLoading()` in non-production | Some teams use other N+1 detection approaches |

---

## Quick reference

| Layer | Class | Responsibility |
|---|---|---|
| HTTP input | `FormRequest` | Validate and map to SubmitDto |
| Input data | `SubmitDto` | Carry typed input to the Action |
| Business logic | `Action` | Execute the use case |
| Output data | `ResultDto` | Carry typed output from the Action |
| API response | `Resource` | Format ResultDto as JSON |
| Web response | `ViewModel` | Format ResultDto for Blade |
| Business errors | `DomainException` | Signal expected failures |
| Application logging | `AppLogger` | Record significant events |
