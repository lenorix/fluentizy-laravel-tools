<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LangExtractCommand extends Command
{
    public $signature = 'lang:extract {locale?}';

    public $description = 'Extract translation strings to lang files';

    public function handle(): int
    {
        $locale = $this->argument('locale');
        $locales = $this->locales($locale);

        if (empty($locales)) {
            $this->error(__('fluentizy-tools::translations.locale-error', [
                'path' => lang_path(),
            ], locale: config('app.locale')));
            return self::FAILURE;
        }

        try {
            $newTranslations = $this->extract();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        foreach ($locales as $locale) {
            gc_collect_cycles();

            $oldTranslations = [];
            $outputFile = lang_path($locale.'.json');

            if (file_exists($outputFile)) {
                $oldTranslations = json_decode(file_get_contents($outputFile), true);
            }

            $translations = [];
            foreach ($newTranslations as $key => $value) {
                $translations[$key] = $oldTranslations[$key] ?? $value;
            }

            file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info(__('fluentizy-tools::translations.ready', [
                'path' => $outputFile,
                'emoji' => $this->emoji($locale),
            ], locale: config('app.locale')));
        }

        return self::SUCCESS;
    }

    /**
     * @param string|null $directory Directory to scan, or null for default directories
     * @return array Extracted translation strings
     * @throws \Exception When file processing fails
     */
    private function extract(?string $directory = null): array
    {
        $directories = [];
        $newTranslations = [];

        if ($directory) {
            $directories[] = $directory;
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
        if (preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]\s*\)/", $content, $matches) === false) {
            $error = 'Processing {$file->getPathname()} failed: ' . error_get_last();
            Log::error($error);
            throw new \Exception($error);
        }
        return $matches[1];
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

    /**
     * @param string|null $locale
     * @return array
     */
    public function locales(?string $locale): array
    {
        if ($locale) {
            return [$locale];
        }

        $locales = [];
        $files = scandir(lang_path());
        foreach ($files as $file) {
            if (str_ends_with($file, '.json')) {
                $locales[] = str_replace('.json', '', $file);
            }
        }
        return $locales;
    }
}
