<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action-dto')]
class MakeDtoCommand extends Command
{
    protected $signature   = 'make:action-dto {name : The DTO name, e.g. Course/CreateCourseData}';
    protected $description = 'Create a new Dto class';

    public function handle(): int
    {
        $name = $this->argument('name');
        assert(is_string($name));

        [$domain, $base] = $this->parseName($name);

        $this->generateDto($domain, $base);

        return self::SUCCESS;
    }

    /** @return array{string|null, string} */
    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $base] = explode('/', $name, 2);

            return [Str::studly($domain), Str::studly($base)];
        }

        return [null, Str::studly($name)];
    }

    private function generateDto(?string $domain, string $base): void
    {
        $className = $base;
        $namespace = $domain ? "App\\Dtos\\{$domain}" : 'App\\Dtos';
        $path      = $domain
            ? app_path("Dtos/{$domain}/{$className}.php")
            : app_path("Dtos/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Dtos\Dto;

        final class {$className} extends Dto
        {
            public function __construct(
                // TODO: add typed properties
            ) {}
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Dto [{$path}] created successfully.");
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
