<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:action')]
class MakeActionCommand extends GeneratorCommand
{
    protected $name        = 'make:action';
    protected $description = 'Create a new Antroly action class (and its unit test)';
    protected $type        = 'Action';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/generators/action.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Actions';
    }

    protected function afterMakeClass(string $name): void
    {
        $this->generateTest($name);
    }

    private function generateTest(string $name): void
    {
        $className = class_basename($name);
        $testPath  = base_path("tests/Unit/Actions/{$className}Test.php");

        if (file_exists($testPath)) {
            return;
        }

        if (! is_dir(dirname($testPath))) {
            mkdir(dirname($testPath), 0755, true);
        }

        file_put_contents($testPath, <<<PHP
<?php

declare(strict_types=1);

it('executes successfully', function () {
    //
});
PHP);

        $this->components->info("Test [{$testPath}] created successfully.");
    }
}
