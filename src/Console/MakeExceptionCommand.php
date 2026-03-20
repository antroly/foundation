<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:domain-exception')]
class MakeExceptionCommand extends Command
{
    protected $signature   = 'make:domain-exception {name : The exception name, e.g. Course/CourseExpired}';
    protected $description = 'Create a new domain exception';

    public function handle(): int
    {
        $name = $this->argument('name');
        assert(is_string($name));
        [$domain, $class] = $this->parseName($name);

        $className = Str::studly($class);

        if (! Str::endsWith($className, 'Exception')) {
            $className .= 'Exception';
        }

        $namespace = $domain
            ? "App\\Exceptions\\{$domain}"
            : 'App\\Exceptions';

        $path = $domain
            ? app_path("Exceptions/{$domain}/{$className}.php")
            : app_path("Exceptions/{$className}.php");

        $errorCode      = $this->toErrorCode($domain, $className);
        $defaultMessage = $this->toDefaultMessage($className);

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Exceptions\DomainException;

        final class {$className} extends DomainException
        {
            public function __construct()
            {
                parent::__construct(
                    '{$defaultMessage}',
                    422,
                    '{$errorCode}',
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Exception [{$path}] created successfully.");

        return self::SUCCESS;
    }

    /** @return array{string|null, string} */
    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);

            return [Str::studly($domain), $class];
        }

        return [null, $name];
    }

    private function toErrorCode(?string $domain, string $className): string
    {
        $base = Str::snake(Str::replaceLast('Exception', '', $className));

        return $domain
            ? Str::snake($domain) . '.' . $base
            : 'exception.' . $base;
    }

    private function toDefaultMessage(string $className): string
    {
        $base  = Str::replaceLast('Exception', '', $className);
        $words = preg_replace('/([A-Z])/', ' $1', $base);

        return ucfirst(strtolower(trim((string) $words))) . '.';
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
