<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Lenorix\FluentizyLaravelTools\Services\GlobeEmoji;

class LangExtractCommand extends Command
{
    public $signature = 'lang:extract
        {--json : Use JSON format instead of PHP}
        {--src=* : Source dir to scan}
        {--out= : Output dir}
        {--filename=translations : Filename for PHP translations files}
        {locale? : Locale to extract translations for}
    ';

    public $description = 'Extract translation strings to lang files ready to be translated or uploaded to fluentizy.lenorix.com';

    public function handle(): int
    {
        $outDir = $this->option('out') ?: null;

        try {
            $srcDirs = $this->srcDirs($this->option('src'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $locales = $this->locales($this->argument('locale'), $outDir, $this->option('json'));
        if (empty($locales)) {
            $this->error(__('fluentizy-tools::translations.locale-error', [
                'path' => lang_path(),
            ], locale: config('app.locale')));

            return self::FAILURE;
        }

        try {
            $newTranslations = $this->extract($srcDirs);
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($locales as $locale) {
            gc_collect_cycles();

            if ($this->option('json')) {
                $outputFile = $this->updateLocaleJson($locale, $newTranslations, $outDir);
            } else {
                $filename = $this->option('filename') ?: 'translations';
                $outputFile = $this->updateLocalePhp($locale, $newTranslations, $outDir, filename: $filename);
            }

            $this->info(__('fluentizy-tools::translations.ready', [
                'path' => $outputFile,
                'emoji' => app(GlobeEmoji::class)->emoji($locale),
            ], locale: config('app.locale')));
        }

        return self::SUCCESS;
    }

    private function updateLocaleJson(string $locale, array $newTranslations, ?string $outDir): string
    {
        $subPath = $locale.'.json';
        $outputFile = $outDir
            ? realpath(rtrim($outDir, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.$subPath
            : lang_path($subPath);

        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = json_decode(file_get_contents($outputFile), true);
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);
        file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $outputFile;
    }

    private function updateLocalePhp(string $locale, array $newTranslations, ?string $outDir, string $filename = 'translations'): string
    {
        $subPath = $locale.'/'.$filename.'.php';
        $outputFile = $outDir
            ? realpath(rtrim($outDir, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.$subPath
            : lang_path($subPath);

        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = include $outputFile;
            if (! is_array($oldTranslations)) {
                $oldTranslations = [];
            }
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);

        $content = "<?php\n\nreturn [";
        foreach ($translations as $key => $value) {
            $escapedKey = str_replace("'", "\\'", $key);
            $escapedValue = str_replace("'", "\\'", $value);
            $content .= "\n    '".$escapedKey."' => '".$escapedValue."',";
        }
        $content .= "\n];\n";

        $dir = dirname($outputFile);
        if (! is_dir($dir)) {
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
     * @return array Extracted translation strings
     *
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
     * @return array Translation strings found in the file
     *
     * @throws \Exception When file processing fails
     */
    private function translationStrings(mixed $file): array
    {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all("/__\(\s*[\'\"](.*?)[\'\"]/", $content, $matches) === false) {
            $error = 'Processing {$file->getPathname()} failed: '.error_get_last();
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

    private function locales(?string $locale, ?string $outDir, bool $json = false): array
    {
        if ($locale) {
            return [$locale];
        }

        $langDir = $outDir ? rtrim($outDir, DIRECTORY_SEPARATOR) : lang_path();
        $files = scandir($langDir);

        $locales = [];
        foreach ($files as $file) {
            if ($json && str_ends_with($file, '.json')) {
                $locales[] = str_replace('.json', '', $file);
            } elseif (is_dir($langDir.DIRECTORY_SEPARATOR.$file) && $file !== '.' && $file !== '..') {
                $locales[] = $file;
            }
        }

        return $locales;
    }

    /**
     * @throws \Exception
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
            } elseif ($realDir === false) {
                throw new \Exception("Source directory not found: {$dir}");
            }
        }

        return $realSourceDirs;
    }
}
