<?php

use Illuminate\Support\Facades\File;

/*
| The Vite entries the views ask for (@vite(...), Vite::asset(...)) must be files that exist and
| that vite.config.js builds: a missing one throws "Unable to locate file in Vite manifest" on the
| page in production, which the tests (withoutVite) would never show. The built manifest itself is
| checked after `npm run build` by check-manifest.mjs (CI job "assets").
*/

/**
 * @return array{entries: array<string, list<string>>, assets: array<string, list<string>>} file => the views that use it
 */
function viteReferences(): array
{
    $references = ['entries' => [], 'assets' => []];

    foreach (File::allFiles(resource_path('views')) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $view = str_replace('\\', '/', $file->getRelativePathname());
        // Blade comments are not rendered (the layout documents @vite('resources/js/pages/x.js') in one).
        $source = (string) preg_replace('/\{\{--.*?--\}\}/s', '', (string) file_get_contents($file->getPathname()));

        preg_match_all('/@vite\s*\((.*?)\)/s', $source, $directives);
        preg_match_all('/<x-optional-vite\b[^>]*:entrypoints="(.*?)"/s', $source, $components);

        foreach ([...$directives[1], ...$components[1]] as $arguments) {
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $arguments, $paths);

            foreach ($paths[1] as $path) {
                $references['entries'][$path][] = $view;
            }
        }

        preg_match_all('/Vite::asset\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $source, $assets);

        foreach ($assets[1] as $path) {
            $references['assets'][$path][] = $view;
        }
    }

    return $references;
}

it('loads only entries that exist and that vite.config.js builds', function (): void {
    $config = (string) file_get_contents(base_path('vite.config.js'));
    $references = viteReferences();
    $problems = [];

    // vite.config.js: the stylesheet, the dashboard script, and every file directly in resources/js/pages.
    expect($config)->toContain("'resources/scss/app.scss'", "'resources/js/app.js'", "readdirSync('resources/js/pages')");

    foreach ($references['entries'] as $path => $views) {
        $built = in_array($path, ['resources/scss/app.scss', 'resources/js/app.js'], true)
            || preg_match('#^resources/js/pages/[^/]+\.js$#', $path) === 1;

        if (! is_file(base_path($path))) {
            $problems[] = "{$path} (".implode(', ', $views).'): no such file';
        } elseif (! $built) {
            $problems[] = "{$path} (".implode(', ', $views).'): not an input of vite.config.js';
        }
    }

    foreach ($references['assets'] as $path => $views) {
        if (! is_file(base_path($path)) && ! str_starts_with($path, 'node_modules/')) {
            $problems[] = "{$path} (".implode(', ', $views).'): no such file';
        }

        if (! str_contains($config, "'{$path}'")) {
            $problems[] = "{$path} (".implode(', ', $views).'): not in the assets of vite.config.js';
        }
    }

    expect($references['entries'])->toHaveKeys(['resources/scss/app.scss', 'resources/js/app.js'])
        ->and($problems)->toBe([]);
});

it('loads every page module from some view', function (): void {
    $used = array_keys(viteReferences()['entries']);

    $unused = collect(File::files(resource_path('js/pages')))
        ->map(fn (SplFileInfo $file): string => 'resources/js/pages/'.$file->getFilename())
        ->filter(fn (string $path): bool => str_ends_with($path, '.js') && ! in_array($path, $used, true))
        ->values()
        ->all();

    // A module no view loads is still built: delete it, or load it with @vite on its page.
    expect($unused)->toBe([]);
});
