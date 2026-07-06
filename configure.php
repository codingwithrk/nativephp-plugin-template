#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Configure a cloned NativePHP Mobile plugin template.
 *
 * Inspired by Spatie's package skeleton configure script, but tailored for a
 * NativePHP plugin that has PHP, Kotlin, Swift, manifest, docs, stubs, and CI
 * placeholders.
 */
final class Configurator
{
    /**
     * @param array<string, string|bool> $options
     */
    public function __construct(
        private readonly array $options,
    ) {
    }

    public function run(): void
    {
        $this->writeln('');
        $this->writeln($this->bold('NativePHP Mobile Plugin Template'));
        $this->writeln('Configure placeholders for your new plugin.');
        $this->writeln('');

        $vendor = $this->slug($this->ask('Composer vendor', $this->option('vendor', $this->guessVendor())));
        $package = $this->slug($this->ask('Composer package', $this->option('package', basename(__DIR__))));
        $plugin = $this->studly($this->ask('Plugin class/name', $this->option('plugin', $this->studly($package))));
        $namespace = $this->ask('PHP namespace', $this->option('namespace', $this->defaultNamespace($vendor, $plugin)));
        $description = $this->ask('Description', $this->option('description', "NativePHP Mobile {$plugin} plugin."));
        $author = $this->ask('Author name', $this->option('author', $this->runCommand('git config user.name')));
        $authorEmail = $this->ask('Author email', $this->option('email', $this->runCommand('git config user.email')));
        $github = $this->slug($this->ask('GitHub owner', $this->option('github', $vendor)));
        $androidPackage = $this->androidSegment($this->ask('Android package segment', $this->option('android-package', $package)));

        $this->writeln('');
        $this->writeln($this->bold('Summary'));
        $this->writeln("Vendor: {$vendor}");
        $this->writeln("Package: {$package}");
        $this->writeln("Plugin: {$plugin}");
        $this->writeln("Namespace: {$namespace}");
        $this->writeln("Android package: com.{$this->androidSegment($vendor)}.{$androidPackage}");
        $this->writeln('');

        if (! $this->confirm('Modify files?', true)) {
            $this->writeln('No files were changed.');

            return;
        }

        $files = $this->files();
        $androidBasePackage = 'com.'.$this->androidSegment($vendor).'.'.$androidPackage;

        foreach ($files as $file) {
            $this->replaceInFile($file, [
                'com.{{ vendor }}.{{ package }}' => $androidBasePackage,
                '{{ vendor }}' => $vendor,
                '{{ package }}' => $package,
                '{{ plugin }}' => $plugin,
                '{{ namespace }}' => $namespace,
                '{{ description }}' => $description,
                '{{ author }}' => $author,
                '{{ author_email }}' => $authorEmail,
                '{{ github }}' => $github,
            ]);
        }

        $this->renamePlaceholderPaths([
            '{{ vendor }}' => $vendor,
            '{{ package }}' => $package,
            '{{ plugin }}' => $plugin,
        ]);

        $this->renameAndroidPackagePath($vendor, $package, $androidPackage);
        $this->normalizeCodeOwners($github);

        $this->writeln($this->green('Updated '.count($files).' files.'));

        if ($this->confirm('Run composer install and tests?', false)) {
            $this->passthru('composer install');
            $this->passthru('composer test');
        }

        if (! $this->hasOption('keep-configure') && $this->confirm('Delete configure.php?', true)) {
            $this->removeConfigureComposerScript();
            unlink(__FILE__);
            $this->writeln($this->green('Deleted configure.php.'));
        }

        $this->writeln('');
        $this->writeln($this->green($this->bold('Your NativePHP plugin is configured.')));
    }

    private function option(string $name, string $default = ''): string
    {
        $value = $this->options[$name] ?? $default;

        return is_string($value) ? $value : $default;
    }

    private function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    private function ask(string $question, string $default = ''): string
    {
        if ($this->hasOption('no-interaction')) {
            return $default;
        }

        $suffix = $default !== '' ? " [{$default}]" : '';
        $answer = readline("{$question}{$suffix}: ");
        $answer = trim((string) $answer);

        return $answer !== '' ? $answer : $default;
    }

    private function confirm(string $question, bool $default): bool
    {
        if ($this->hasOption('no-interaction')) {
            return $default;
        }

        $defaultLabel = $default ? 'Y/n' : 'y/N';
        $answer = strtolower(trim((string) readline("{$question} [{$defaultLabel}]: ")));

        if ($answer === '') {
            return $default;
        }

        return in_array($answer, ['y', 'yes'], true);
    }

    /**
     * @return list<string>
     */
    private function files(): array
    {
        $directory = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = [];

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $relativePath = $this->relativePath($path);

            if ($this->shouldSkipPath($relativePath)) {
                continue;
            }

            $contents = file_get_contents($path);

            if (is_string($contents) && str_contains($contents, '{{ ')) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * @param array<string, string> $replacements
     */
    private function replaceInFile(string $path, array $replacements): void
    {
        $contents = file_get_contents($path);

        if (is_string($contents)) {
            file_put_contents($path, str_replace(array_keys($replacements), array_values($replacements), $contents));
        }
    }

    /**
     * @param array<string, string> $replacements
     */
    private function renamePlaceholderPaths(array $replacements): void
    {
        $paths = [];
        $directory = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $item) {
            if (! $item instanceof SplFileInfo) {
                continue;
            }

            $path = $item->getPathname();
            $relativePath = $this->relativePath($path);

            if (! $this->shouldSkipPath($relativePath) && str_contains($path, '{{ ')) {
                $paths[] = $path;
            }
        }

        usort($paths, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($paths as $path) {
            $newPath = str_replace(array_keys($replacements), array_values($replacements), $path);

            if ($newPath === $path || ! file_exists($path)) {
                continue;
            }

            if (! is_dir(dirname($newPath))) {
                mkdir(dirname($newPath), 0777, true);
            }

            rename($path, $newPath);
        }
    }

    private function renameAndroidPackagePath(string $vendor, string $package, string $androidPackage): void
    {
        $composerPath = __DIR__.DIRECTORY_SEPARATOR.'android'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main'.DIRECTORY_SEPARATOR.'kotlin'.DIRECTORY_SEPARATOR.'com'.DIRECTORY_SEPARATOR.$vendor.DIRECTORY_SEPARATOR.$package;

        if (! is_dir($composerPath)) {
            return;
        }

        $nativePath = __DIR__.DIRECTORY_SEPARATOR.'android'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main'.DIRECTORY_SEPARATOR.'kotlin'.DIRECTORY_SEPARATOR.'com'.DIRECTORY_SEPARATOR.$this->androidSegment($vendor).DIRECTORY_SEPARATOR.$androidPackage;

        if (! is_dir(dirname($nativePath))) {
            mkdir(dirname($nativePath), 0777, true);
        }

        rename($composerPath, $nativePath);
    }

    private function normalizeCodeOwners(string $github): void
    {
        $path = __DIR__.DIRECTORY_SEPARATOR.'.github'.DIRECTORY_SEPARATOR.'CODEOWNERS';

        if (! file_exists($path)) {
            return;
        }

        $contents = file_get_contents($path);

        if (is_string($contents)) {
            file_put_contents($path, preg_replace('/@[^\\s]+/', '@'.$github, $contents) ?? $contents);
        }
    }

    private function removeConfigureComposerScript(): void
    {
        $path = __DIR__.DIRECTORY_SEPARATOR.'composer.json';

        if (! file_exists($path)) {
            return;
        }

        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            return;
        }

        $composer = json_decode($contents, true);

        if (! is_array($composer)) {
            return;
        }

        unset($composer['scripts']['configure']);

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    private function shouldSkipPath(string $relativePath): bool
    {
        $segments = explode(DIRECTORY_SEPARATOR, $relativePath);

        return in_array($segments[0] ?? '', ['.git', 'vendor', 'node_modules'], true)
            || $relativePath === basename(__FILE__);
    }

    private function relativePath(string $path): string
    {
        return str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $path);
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;

        return trim($value, '-');
    }

    private function androidSegment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_]+/', '', $value) ?? $value;

        return $value !== '' ? $value : 'plugin';
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords(strtolower($value));

        return str_replace(' ', '', $value);
    }

    private function defaultNamespace(string $vendor, string $plugin): string
    {
        return $this->studly($vendor).'\\'.$plugin;
    }

    private function guessVendor(): string
    {
        $remote = $this->runCommand('git config remote.origin.url');

        if ($remote !== '') {
            $parts = explode('/', str_replace(':', '/', trim($remote)));
            $owner = $parts[count($parts) - 2] ?? '';

            if ($owner !== '') {
                return $owner;
            }
        }

        return 'vendor';
    }

    private function runCommand(string $command): string
    {
        $output = shell_exec($command);

        return trim(is_string($output) ? $output : '');
    }

    private function passthru(string $command): void
    {
        passthru($command, $exitCode);

        if ($exitCode !== 0) {
            exit($exitCode);
        }
    }

    private function writeln(string $message): void
    {
        fwrite(STDOUT, $message.PHP_EOL);
    }

    private function bold(string $message): string
    {
        return $this->ansi('1', $message);
    }

    private function green(string $message): string
    {
        return $this->ansi('32', $message);
    }

    private function ansi(string $code, string $message): string
    {
        if (! function_exists('posix_isatty') || ! posix_isatty(STDOUT)) {
            return $message;
        }

        return "\033[{$code}m{$message}\033[0m";
    }
}

/**
 * @param list<string> $arguments
 *
 * @return array<string, string|bool>
 */
function parseOptions(array $arguments): array
{
    $options = [];

    foreach (array_slice($arguments, 1) as $argument) {
        if (! str_starts_with($argument, '--')) {
            continue;
        }

        $argument = substr($argument, 2);

        if (! str_contains($argument, '=')) {
            $options[$argument] = true;

            continue;
        }

        [$key, $value] = explode('=', $argument, 2);
        $options[$key] = $value;
    }

    return $options;
}

(new Configurator(parseOptions($argv)))->run();
