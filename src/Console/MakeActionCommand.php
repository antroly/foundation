<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action')]
class MakeActionCommand extends Command
{
    protected $signature   = 'make:action {name : The action name, e.g. Course/CreateCourse}';
    protected $description = 'Create a new Action with a test';

    public function handle(): int
    {
        $name = $this->argument('name');
        assert(is_string($name));
        [$domain, $action] = $this->parseName($name);

        $this->generateAction($domain, $action);
        $this->generateTest($domain, $action);

        return self::SUCCESS;
    }

    /** @return array{string, string} */
    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $action] = explode('/', $name, 2);

            return [Str::studly($domain), Str::studly($action)];
        }

        $action = Str::studly($name);

        return [$action, $action];
    }

    private function generateAction(string $domain, string $action): void
    {
        $className = "{$action}Action";
        $namespace = "App\\Actions\\{$domain}";
        $path      = app_path("Actions/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Actions\Action;

        final class {$className} extends Action
        {
            public function execute(): mixed
            {
                // TODO: implement
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Action [{$path}] created successfully.");
    }

    private function generateTest(string $domain, string $action): void
    {
        $className = "{$action}Action";
        $namespace = "App\\Actions\\{$domain}";
        $path      = base_path("tests/Unit/Actions/{$domain}/{$className}Test.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        use {$namespace}\\{$className};

        describe('{$className}', function () {
            it('executes', function () {
                //
            });
        });
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Test [{$path}] created successfully.");
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
