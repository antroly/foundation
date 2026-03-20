<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'antroly:install')]
class InstallCommand extends Command
{
    protected $signature   = 'antroly:install';
    protected $description = 'Install the Antroly Foundation into your Laravel application';

    public function handle(): int
    {
        $this->components->info('Installing Antroly Foundation...');
        $this->newLine();

        $this->components->task('Publishing foundation stubs', function () {
            $this->callSilently('vendor:publish', ['--tag' => 'antroly-foundation']);
        });

        $this->components->task('Publishing architecture tests', function () {
            $this->callSilently('vendor:publish', ['--tag' => 'antroly-tests']);
        });

        if ($this->confirm('Publish the activity log migration?', true)) {
            $this->components->task('Publishing migration', function () {
                $this->callSilently('vendor:publish', ['--tag' => 'antroly-migrations']);
            });
        }

        $this->newLine();
        $this->components->info('Antroly Foundation installed successfully.');
        $this->newLine();
        $this->line('  <fg=gray>Next steps:</>');
        $this->newLine();
        $this->line('  1. Register <comment>AppServiceProvider</comment> in <comment>bootstrap/app.php</comment>:');
        $this->newLine();
        $this->line('       <fg=gray>->withProviders([App\Providers\AppServiceProvider::class])</>');
        $this->newLine();
        $this->line('  2. Register <comment>AppExceptionHandler</comment> in <comment>bootstrap/app.php</comment>:');
        $this->newLine();
        $this->line('       <fg=gray>->withExceptions(function (Exceptions $exceptions) {</>');
        $this->line('       <fg=gray>    App\Exceptions\AppExceptionHandler::register($exceptions);</>');
        $this->line('       <fg=gray>})</>');
        $this->newLine();
        $this->line('  3. Run <comment>php artisan migrate</comment>');
        $this->newLine();

        return self::SUCCESS;
    }
}
