<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LangExtractCommand extends Command
{
    public $signature = 'lang:extract {--json : Use JSON format} {--src=* : Source dir to scan} {locale? : Locale to extract translations for}';

    public $description = 'Extract translation strings to lang files';

    public function handle(): int
    {
        $locales = $this->locales($this->argument('locale'), $this->option('json'));
        if (empty($locales)) {
            $this->error(__('fluentizy-tools::translations.locale-error', [
                'path' => lang_path(),
            ], locale: config('app.locale')));
            return self::FAILURE;
        }

        try {
            $newTranslations = $this->extract($this->srcDirs($this->option('src')));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        foreach ($locales as $locale) {
            gc_collect_cycles();

            if ($this->option('json')) {
                $outputFile = $this->updateLocaleJson($locale, $newTranslations);
            } else {
                $outputFile = $this->updateLocalePhp($locale, $newTranslations);
            }

            $this->info(__('fluentizy-tools::translations.ready', [
                'path' => $outputFile,
                'emoji' => $this->emoji($locale),
            ], locale: config('app.locale')));
        }
        return self::SUCCESS;
    }

    /**
     * @param string $locale
     * @param array $newTranslations
     * @return string
     */
    private function updateLocaleJson(string $locale, array $newTranslations): string
    {
        $outputFile = lang_path($locale.'.json');
        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = json_decode(file_get_contents($outputFile), true);
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);
        file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $outputFile;
    }

    private function updateLocalePhp(string $locale, array $newTranslations, string $filename = 'translations'): string
    {
        $outputFile = lang_path($locale . '/' . $filename . '.php');
        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = include $outputFile;
            if (!is_array($oldTranslations)) {
                $oldTranslations = [];
            }
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);

        $content = "<?php\n\nreturn [";
        foreach ($translations as $key => $value) {
            $escapedKey = str_replace("'", "\\'", $key);
            $escapedValue = str_replace("'", "\\'", $value);
            $content .= "\n    '" . $escapedKey . "' => '" . $escapedValue . "',";
        }
        $content .= "\n];\n";

        $dir = dirname($outputFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($outputFile, $content);
        return $outputFile;
    }

    private function recoverPreviousTranslations(array $oldTranslations, array $newTranslations): array
    {
        $translations = [];
        foreach ($newTranslations as $key => $value) {
            $translations[$key] = $oldTranslations[$key] ?? $value;
        }
        return $translations;
    }

    /**
     * @param array|null $sourceDirs
     * @return array Extracted translation strings
     * @throws \Exception When file processing fails
     */
    private function extract(?array $sourceDirs = null): array
    {
        $directories = [];
        $newTranslations = [];

        if ($sourceDirs) {
            $directories = $sourceDirs;
        } else {
            $directories[] = base_path('app');
            $directories[] = base_path('routes');
            $directories[] = base_path('config');
            $directories[] = base_path('resources/views');
        }

        foreach ($directories as $directory) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                    foreach ($this->translationStrings($file) as $key) {
                        if (! isset($newTranslations[$key])) {
                            $newTranslations[$key] = $key;
                        }
                    }
                }
            }
        }

        ksort($newTranslations);
        return $newTranslations;
    }

    /**
     * @param mixed $file
     * @return array Translation strings found in the file
     * @throws \Exception When file processing fails
     */
    private function translationStrings(mixed $file): array
    {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]/", $content, $matches) === false) {
            $error = 'Processing {$file->getPathname()} failed: ' . error_get_last();
            Log::error($error);
            throw new \Exception($error);
        }
        return array_map(function ($item) {
            if (str_contains($item, '::')) {
                $item = explode('::', $item, 2)[1];
                $parts = explode('.', $item);
                array_shift($parts);
                $item = implode('.', $parts);
            }
            return $item;
        }, $matches[1]);
    }

    /**
     * @param string|null $locale
     * @return array
     */
    private function locales(?string $locale, bool $json=false): array
    {
        if ($locale) {
            return [$locale];
        }

        $locales = [];
        $files = scandir(lang_path());
        foreach ($files as $file) {
            if ($json && str_ends_with($file, '.json')) {
                $locales[] = str_replace('.json', '', $file);
            } elseif (is_dir(lang_path($file)) && $file !== '.' && $file !== '..') {
                $locales[] = $file;
            }
        }
        return $locales;
    }

    /**
     * @param array|null $sourceDirs
     * @return array|null
     */
    private function srcDirs(?array $sourceDirs): ?array
    {
        if (empty($sourceDirs)) {
            return null;
        }

        $realSourceDirs = [];
        foreach ($sourceDirs as $dir) {
            $realDir = realpath($dir);
            if ($realDir && is_dir($realDir)) {
                $realSourceDirs[] = $realDir;
            }
        }
        return $realSourceDirs;
    }

    private function emoji(string $locale): string
    {
        [$language, $country] = explode('_', $locale.'_');
        $language = strtolower($language);
        $country = strtolower($country);

        $globes = [
            '🌍' => [
                'en', 'de', 'fr', 'it', 'es', 'pt', 'nl', 'sv', 'no', 'da', 'fi', 'wo', 'ff', 'ts', 'sn', 'ny',
                'tr', 'pl', 'cs', 'hu', 'ro', 'sk', 'sl', 'hr', 'sr', 'bg', 'el', 'om', 'ti', 'tn', 'mg', 'ss',
                'is', 'ga', 'cy', 'eu', 'gl', 'ca', 'af', 'sw', 'zu', 'xh', 'st', 'lg', 've', 'rw', 'so', 'bm',
                'ru', 'uk', 'be', 'et', 'lv', 'lt', 'sq', 'mk', 'mt', 'ar', 'am',
            ],
            '🌎' => [
                'bn', 'ta', 'te', 'ml', 'kn', 'mr', 'qu', 'gn', 'ay', 'ha', 'br', 'ht', 'km', 'lo', 'my', 'ha',
                'gu', 'pa', 'si', 'ne', 'yo', 'ig',
            ],
            '🌏' => [
                'zh', 'ja', 'ko', 'kk', 'mn', 'vi', 'th', 'fa', 'ur', 'he', 'id', 'ty', 'mi', 'sm', 'to', 'az',
                'ms', 'tl', 'su', 'jv', 'my', 'km', 'lo', 'hi', 'mr', 'ml', 'kn', 'fj', 'ka', 'te', 'ta', 'hy',
            ],
            '🌐' => [
                'eo', 'ia',
            ],
        ];

        foreach ([$country, $language] as $code) {
            foreach ($globes as $emoji => $codes) {
                if (in_array($code, $codes)) {
                    return $emoji;
                }
            }
        }

        return '🌐';
    }
}
