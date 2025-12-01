<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;

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

    private function extract(): array
    {
        $directories = [
            base_path('app'),
            base_path('routes'),
            base_path('config'),
            base_path('resources/views'),
        ];
        $newTranslations = [];

        foreach ($directories as $directory) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                    $content = file_get_contents($file->getPathname());
                    preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]\s*\)/", $content, $matches);

                    foreach ($matches[1] as $key) {
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
            ],
            '🌎' => [
                'ar', 'he', 'fa', 'ur', 'hi', 'bn', 'ta', 'te', 'ml', 'kn', 'mr',
                'gu', 'pa', 'si', 'ne', 'am', 'sw', 'yo', 'ig', 'ha', 'zu', 'xh',
                'br', 'ht', 'yo', 'ig', 'ha', 'km', 'lo', 'my', 'th', 'vi', 'id',
            ],
            '🌏' => [
                'ru', 'zh', 'ja', 'ko', 'uk', 'be', 'kk', 'mn', 'vi', 'th', 'id',
                'ms', 'tl', 'su', 'jv', 'my', 'km', 'lo', 'et', 'lv', 'lt', 'hr',
                'sr', 'bg', 'el', 'hy', 'az', 'ka', 'te', 'ta', 'ml', 'kn', 'mr',
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
