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
        $locales = [];

        if ($locale) {
            $locales[] = $locale;
        } else {
            $files = scandir(lang_path());
            foreach ($files as $file) {
                if (str_ends_with($file, '.json')) {
                    $locales[] = str_replace('.json', '', $file);
                }
            }

            if (empty($locales)) {
                $this->error(__('fluentizy-tools::translations.locale-error', [
                    'path' => lang_path(),
                ], locale: config('app.locale')));

                return self::FAILURE;
            }
        }

        $newTranslations = $this->extract();
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
                    $matches = $this->translationStrings($file);

                    foreach ($matches as $key) {
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
                'en', 'de', 'fr', 'it', 'es', 'pt', 'nl', 'sv', 'no', 'da', 'fi',
                'tr', 'pl', 'cs', 'hu', 'ro', 'sk', 'sl', 'hr', 'sr', 'bg', 'el',
                'is', 'ga', 'cy', 'eu', 'gl', 'ca', 'af', 'sw', 'zu', 'xh', 'st',
                'ru', 'uk', 'be', 'et', 'lv', 'lt', 'sq', 'mk', 'mt', 'ar', 'am',
                'so', 'mg', 'sn', 'ny', 'rw', 'tn', 'ts', 'ss', 've', 'ti', 'ff',
                'wo', 'bm', 'lg', 'om',
            ],
            '🌎' => [
                'bn', 'ta', 'te', 'ml', 'kn', 'mr', 'qu', 'gn', 'ay', 'ha', 'br',
                'gu', 'pa', 'si', 'ne', 'yo', 'ig', 'ha', 'km', 'lo', 'my', 'ht',
            ],
            '🌏' => [
                'zh', 'ja', 'ko', 'kk', 'mn', 'vi', 'th', 'fa', 'ur', 'he', 'id',
                'ms', 'tl', 'su', 'jv', 'my', 'km', 'lo', 'hi', 'mr', 'ml', 'kn',
                'hy', 'az', 'ka', 'te', 'ta', 'mi', 'sm', 'to', 'fj', 'ty',
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

        return array_rand($globes);
    }
}
