<?php

namespace Lenorix\FluentizyLaravelTools\Services;

use Illuminate\Support\Facades\Log;

class TranslationsExtractor
{
    /**
     * @return array Extracted translation strings
     *
     * @throws \Exception When file processing fails
     */
    public function fromDirs(?array $sourceDirs = null): array
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
                    foreach ($this->fromFile($file) as $key) {
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
     * @return array Translation strings found in the file
     *
     * @throws \Exception When file processing fails
     */
    public function fromFile(mixed $file): array
    {
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            $error = "Processing {$file->getPathname()} failed: ".error_get_last();
            Log::error($error);
            throw new \Exception($error);
        }

        return $this->fromString($content);
    }

    /**
     * @return array|string[]
     *
     * @throws \Exception
     */
    public function fromString(string $content): array
    {
        $translations = [];
        $functions = [
            '__',
            'trans',
            '@lang',
        ];

        foreach ($functions as $function) {
            if (preg_match_all("/".$function."\(\s*['\"](.*?)['\"]/", $content, $matches) === false) {
                $error = 'Processing failed: ' . error_get_last();
                Log::error($error);
                throw new \Exception($error);
            }

            $newTranslations = array_map(function ($item) {
                // Packages translations start with 'package::file.' and must be stripped.
                if (str_contains($item, '::')) {
                    $item = explode('::', $item, 2)[1];
                    $parts = explode('.', $item);
                    array_shift($parts);
                    $item = implode('.', $parts);
                }

                return $item;
            }, $matches[1]);

            foreach ($newTranslations as $key) {
                if (!isset($translations[$key])) {
                    $translations[$key] = $key;
                }
            }
        }

        return $translations;
    }
}
