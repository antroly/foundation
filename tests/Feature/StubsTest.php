<?php

declare(strict_types=1);

$stubsBase = realpath(__DIR__ . '/../../stubs');

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($stubsBase, RecursiveDirectoryIterator::SKIP_DOTS)
);

$dataset = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $key = str_replace($stubsBase . '/', '', $file->getPathname());
        $dataset[$key] = [$file->getPathname()];
    }
}

it('stub is valid PHP: <filename>', function (string $path) {
    $output = shell_exec("php -l {$path} 2>&1");
    expect($output)->toContain('No syntax errors detected');
})->with($dataset);
