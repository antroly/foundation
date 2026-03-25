<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action-request')]
class MakeActionRequestCommand extends Command
{
    protected $signature   = 'make:action-request {name : The request name, e.g. Course/CreateCourse}';
    protected $description = 'Create a new Action Request class';

    public function handle(): int
    {
        $name = $this->argument('name');

        assert(is_string($name));

        [$domain, $base] = $this->parseName($name);

        $className = Str::studly($base) . 'Request';
        $dtoClass  = Str::studly($base) . 'Data';
        $namespace = $domain ? "App\\Http\\Requests\\{$domain}" : 'App\\Http\\Requests';
        $dtoNs     = $domain ? "App\\Dtos\\{$domain}" : 'App\\Dtos';

        $path = $domain
            ? app_path("Http/Requests/{$domain}/{$className}.php")
            : app_path("Http/Requests/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use {$dtoNs}\\{$dtoClass};
        use App\Http\Requests\ActionRequest;

        final class {$className} extends ActionRequest
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

            public function toDto(): {$dtoClass}
            {
                return new {$dtoClass}(
                    // TODO: map from \$this->validated()
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Request [{$path}] created successfully.");

        return self::SUCCESS;
    }

    /** @return array{string|null, string} */
    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $base] = explode('/', $name, 2);

            return [Str::studly($domain), $base];
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
