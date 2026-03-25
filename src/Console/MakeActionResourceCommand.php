<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action-resource')]
class MakeActionResourceCommand extends Command
{
    protected $signature   = 'make:action-resource {name : The resource name, e.g. Course/CreateCourse}
                              {--type=api : Presentation type: api (default) or web}';
    protected $description = 'Create a new Action Resource (API Resource or ViewModel)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $type = $this->option('type');

        assert(is_string($name));
        assert(is_string($type));

        if (! in_array($type, ['api', 'web'], true)) {
            $this->components->error("Invalid --type value \"{$type}\". Allowed values: api, web.");

            return self::FAILURE;
        }

        [$domain, $base] = $this->parseName($name);

        $type === 'web'
            ? $this->generateViewModel($domain, Str::studly($base))
            : $this->generateResource($domain, Str::studly($base));

        return self::SUCCESS;
    }

    private function generateResource(?string $domain, string $base): void
    {
        $className = Str::endsWith($base, 'Resource') ? $base : "{$base}Resource";
        $namespace = $domain ? "App\\Http\\Resources\\{$domain}" : 'App\\Http\\Resources';

        $path = $domain
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
                    // TODO: map from \$this->resource (Dto)
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Resource [{$path}] created successfully.");
    }

    private function generateViewModel(?string $domain, string $base): void
    {
        $className = Str::endsWith($base, 'ViewModel') ? $base : "{$base}ViewModel";
        $namespace = $domain ? "App\\Http\\ViewModels\\{$domain}" : 'App\\Http\\ViewModels';

        $path = $domain
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
                    // TODO: map from \$this->data (Dto)
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("ViewModel [{$path}] created successfully.");
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
