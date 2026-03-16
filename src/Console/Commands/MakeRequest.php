<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRequest extends Command
{
    protected $signature = 'make:antroly-request {name : The request name, e.g. Course/CreateCourseRequest}';

    protected $description = 'Create a new FormRequest that maps to a SubmitDto';

    public function handle(): int
    {
        $name = $this->argument('name');

        [$domain, $class] = $this->parseName($name);

        $className = Str::studly($class);
        $namespace = $domain ? "App\\Http\\Requests\\{$domain}" : 'App\\Http\\Requests';
        $dtoNs     = $domain ? "App\\Dtos\\{$domain}" : 'App\\Dtos';
        $submitDto = Str::replaceLast('Request', 'SubmitDto', $className);
        $path      = $domain
            ? app_path("Http/Requests/{$domain}/{$className}.php")
            : app_path("Http/Requests/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use {$dtoNs}\\{$submitDto};
        use Illuminate\Foundation\Http\FormRequest;

        final class {$className} extends FormRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function rules(): array
            {
                return [
                    // TODO: add validation rules
                ];
            }

            public function toDto(): {$submitDto}
            {
                return new {$submitDto}(
                    // TODO: map validated fields
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Request [{$path}] created successfully.");

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);
            return [Str::studly($domain), $class];
        }

        return [null, $name];
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->components->warn("File [{$path}] already exists. Skipping.");
            return;
        }

        file_put_contents($path, $content);
    }
}
