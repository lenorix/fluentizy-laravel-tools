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
            if (! is_dir($directory)) {
                Log::warning("Directory does not exist or is not accessible: {$directory}");

                continue;
            }

            try {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $filename = $file->getFilename();
                        // Check for .php files and .blade.php files
                        if (str_ends_with($filename, '.php') || str_ends_with($filename, '.blade.php')) {
                            foreach ($this->fromFile($file) as $key) {
                                if (! isset($newTranslations[$key])) {
                                    $newTranslations[$key] = $key;
                                }
                            }
                        }
                    }
                }
            } catch (\UnexpectedValueException $e) {
                Log::warning("Failed to read directory: {$directory} - ".$e->getMessage());

                continue;
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
        $pathname = $file->getPathname();
        $content = file_get_contents($pathname);
        if ($content === false) {
            $lastError = error_get_last();
            $error = "Processing {$pathname} failed: ".($lastError['message'] ?? 'Unknown error');
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
        if (preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]/", $content, $matches) === false) {
            $lastError = error_get_last();
            $error = 'Processing content failed: '.($lastError['message'] ?? 'Unknown preg_match_all error');
            Log::error($error);
            throw new \Exception($error);
        }

        return array_map(function ($item) {
            // Packages translations start with 'package::file.' and must be stripped.
            if (str_contains($item, '::')) {
                $item = explode('::', $item, 2)[1];
                $parts = explode('.', $item);
                array_shift($parts);
                $item = implode('.', $parts);
            }

            return $item;
        }, $matches[1]);
    }
}
