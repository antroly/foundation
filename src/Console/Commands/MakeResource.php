<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeResource extends Command
{
    protected $signature = 'make:antroly-resource {name : The resource name, e.g. Course/CourseResource}
                            {--web : Generate a ViewModel instead of an API Resource}';

    protected $description = 'Create a new Resource (API) or ViewModel (web)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $web  = $this->option('web');

        [$domain, $class] = $this->parseName($name);

        $web
            ? $this->generateViewModel($domain, $class)
            : $this->generateResource($domain, $class);

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);
            return [Str::studly($domain), Str::studly($class)];
        }

        return [null, Str::studly($name)];
    }

    private function generateResource(?string $domain, string $class): void
    {
        $className = Str::endsWith($class, 'Resource') ? $class : "{$class}Resource";
        $namespace = $domain ? "App\\Http\\Resources\\{$domain}" : 'App\\Http\\Resources';
        $path      = $domain
            ? app_path("Http/Resources/{$domain}/{$className}.php")
            : app_path("Http/Resources/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Http\Resources\BaseResource;
        use Illuminate\Http\Request;

        final class {$className} extends BaseResource
        {
            /**
             * @return array<string, mixed>
             */
            public function toArray(Request \$request): array
            {
                return [
                    // TODO: map result DTO properties
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Resource [{$path}] created successfully.");
    }

    private function generateViewModel(?string $domain, string $class): void
    {
        $className = Str::endsWith($class, 'ViewModel') ? $class : "{$class}ViewModel";
        $namespace = $domain ? "App\\Http\\ViewModels\\{$domain}" : 'App\\Http\\ViewModels';
        $path      = $domain
            ? app_path("Http/ViewModels/{$domain}/{$className}.php")
            : app_path("Http/ViewModels/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Http\ViewModels\BaseViewModel;

        final class {$className} extends BaseViewModel
        {
            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return [
                    // TODO: map result DTO properties
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("ViewModel [{$path}] created successfully.");
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
