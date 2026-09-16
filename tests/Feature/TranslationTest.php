<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;

test('all translatable strings in src exist in locale json files', function () {
    $locales = ['pt_BR', 'es', 'ja', 'zh_CN', 'de', 'fr'];
    $srcPath = realpath(__DIR__.'/../../src');

    // 1. Scan src/ directory for __('...') strings
    $extractedKeys = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcPath));

    foreach ($iterator as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        // Match __('string') or __("string") call sites
        preg_match_all("/__\(\s*['\"]([^'\"]+)['\"]/u", $content, $matches);

        if (! empty($matches[1])) {
            foreach ($matches[1] as $key) {
                $extractedKeys[] = $key;
            }
        }
    }

    $extractedKeys = array_unique($extractedKeys);

    // 2. Assert every extracted key is present in each locale JSON file
    foreach ($locales as $locale) {
        $jsonPath = __DIR__."/../../lang/{$locale}.json";

        expect(file_exists($jsonPath))->toBeTrue("Missing translation file: lang/{$locale}.json");

        /** @var array<string, string> $translations */
        $translations = json_decode(file_get_contents($jsonPath), true);

        foreach ($extractedKeys as $key) {
            expect($translations)->toHaveKey($key);
        }
    }
});

test('commands render portuguese output when locale is set to pt_BR', function () {
    App::setLocale('pt_BR');

    $this->artisan('honeyblock:list')
        ->expectsOutputToContain('Nenhum IP bloqueado ativo encontrado.')
        ->assertExitCode(0);

    $this->artisan('honeyblock:whitelist')
        ->expectsOutputToContain('Nenhum IP encontrado na lista branca.')
        ->assertExitCode(0);

    App::setLocale('en');
});
