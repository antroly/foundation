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
    protected $description = 'Create a new Action with SubmitDto and ResultDto';

    public function handle(): int
    {
        $name = $this->argument('name');
        assert(is_string($name));
        [$domain, $action] = $this->parseName($name);

        $this->generateAction($domain, $action);
        $this->generateSubmitDto($domain, $action);
        $this->generateResultDto($domain, $action);

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
        $submitDto = "{$action}SubmitDto";
        $resultDto = "{$action}ResultDto";
        $namespace = "App\\Actions\\{$domain}";
        $dtoNs     = "App\\Dtos\\{$domain}";
        $path      = app_path("Actions/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Actions\Action;
        use {$dtoNs}\\{$submitDto};
        use {$dtoNs}\\{$resultDto};

        final class {$className} extends Action
        {
            public function execute({$submitDto} \$dto): {$resultDto}
            {
                // TODO: implement
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Action [{$path}] created successfully.");
    }

    private function generateSubmitDto(string $domain, string $action): void
    {
        $className = "{$action}SubmitDto";
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

    private function generateResultDto(string $domain, string $action): void
    {
        $className = "{$action}ResultDto";
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
