<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:dto')]
class MakeDtoCommand extends Command
{
    protected $signature   = 'make:dto {name : The DTO name, e.g. Course/CreateCourse} {--type=submit : The DTO type: submit or result}';
    protected $description = 'Create a SubmitDto or ResultDto';

    public function handle(): int
    {
        $name = $this->argument('name');
        assert(is_string($name));
        $type = $this->option('type');
        assert(is_string($type));

        if (! in_array($type, ['submit', 'result'], true)) {
            $this->components->error('Invalid type. Use --type=submit or --type=result.');

            return self::FAILURE;
        }

        [$domain, $base] = $this->parseName($name);

        if ($type === 'submit') {
            $this->generateSubmitDto($domain, $base);
        } else {
            $this->generateResultDto($domain, $base);
        }

        return self::SUCCESS;
    }

    /** @return array{string, string} */
    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $base] = explode('/', $name, 2);

            return [Str::studly($domain), Str::studly($base)];
        }

        $base = Str::studly($name);

        return [$base, $base];
    }

    private function generateSubmitDto(string $domain, string $base): void
    {
        $className = "{$base}SubmitDto";
        $namespace = "App\\Dtos\\{$domain}";
        $path      = app_path("Dtos/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Contracts\Dto\FromRequest;
        use Illuminate\Foundation\Http\FormRequest;

        final class {$className} implements FromRequest
        {
            public function __construct(
                // TODO: add typed properties
            ) {}

            public static function fromRequest(FormRequest \$request): static
            {
                return new static(
                    // TODO: map validated fields
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("SubmitDto [{$path}] created successfully.");
    }

    private function generateResultDto(string $domain, string $base): void
    {
        $className = "{$base}ResultDto";
        $namespace = "App\\Dtos\\{$domain}";
        $path      = app_path("Dtos/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Contracts\Dto\ResultData;

        final class {$className} implements ResultData
        {
            public function __construct(
                // TODO: add typed properties
            ) {}
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("ResultDto [{$path}] created successfully.");
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
