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

Actions are the only place where business logic lives.

**An Action must:**
- Extend `App\Actions\Action`
- Implement a single `execute()` method
- Accept a typed Submit DTO as input
- Return a typed Result DTO as output
- Be `final`

**An Action may:**
- Use Eloquent models to read and persist data
- Call other Actions
- Call Guards or Validators
- Throw Domain Exceptions
- Inject dependencies via the constructor

**An Action must not:**
- Return an Eloquent model
- Accept an HTTP request object
- Call `response()`, `redirect()`, or any HTTP helper
- Contain presentation logic
- Know whether the caller is an API or a web request

```php
// correct
final class EnrollStudentAction extends Action
{
    public function execute(EnrollStudentSubmitDto $dto): EnrollmentResultDto
    {
        // business logic here
    }
}

// wrong — returning a model
final class EnrollStudentAction extends Action
{
    public function execute(EnrollStudentSubmitDto $dto): Enrollment
    {
        // ...
    }
}
```

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
