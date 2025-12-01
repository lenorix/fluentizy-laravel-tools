<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;

class LangExtractCommand extends Command
{
    public $signature = 'lang:extract {locale}';

    public $description = 'Extract translation strings to lang files';

    public function handle(): int
    {
        $locale = $this->argument('locale');
        $directories = [
            base_path('app'),
            base_path('routes'),
            base_path('config'),
            base_path('resources/views'),
        ];
        $outputFile = lang_path($locale . '.json');

        $translations = [];

        foreach ($directories as $directory) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                    $content = file_get_contents($file->getPathname());

                    // Regex to find all instances of __()
                    preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]\s*\)/", $content, $matches);

                    // Store the results
                    foreach ($matches[1] as $key) {
                        if (! isset($translations[$key])) {
                            $translations[$key] = $key;  // Initial extraction without translation
                        }
                    }
                }
            }
        }

        ksort($translations);

        file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT));
        $this->comment($this->emoji($locale) . '  ' . $outputFile);
        return self::SUCCESS;
    }

    private function emoji(string $locale): string
    {
        [$language, $country] = explode('_', $locale . '_');
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
            // globe for esperanto, interlingua, etc
            '🌐' => [
                'eo',
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
