<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Lenorix\FluentizyLaravelTools\Services\Formats\JsonTranslations;
use Lenorix\FluentizyLaravelTools\Services\Formats\PhpTranslations;
use Lenorix\FluentizyLaravelTools\Services\GlobeEmoji;
use Lenorix\FluentizyLaravelTools\Services\TranslationsExtractor;

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
            $newTranslations = app(TranslationsExtractor::class)->fromDirs($srcDirs);
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

    /**
     * @throws \Exception When file read/write fails
     */
    private function updateLocaleJson(string $locale, array $newTranslations, ?string $outDir): string
    {
        $subPath = $locale.'.json';
        $outputFile = $outDir
            ? realpath(rtrim($outDir, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.$subPath
            : lang_path($subPath);

        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = app(JsonTranslations::class)->loadFromFile($outputFile);
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);
        app(JsonTranslations::class)->saveToFile($translations, $outputFile);

        return $outputFile;
    }

    /**
     * @throws \Exception When file read/write fails
     */
    private function updateLocalePhp(string $locale, array $newTranslations, ?string $outDir, string $filename = 'translations'): string
    {
        $subPath = $locale.'/'.$filename.'.php';
        $outputFile = $outDir
            ? realpath(rtrim($outDir, DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.$subPath
            : lang_path($subPath);

        $oldTranslations = [];
        if (file_exists($outputFile)) {
            $oldTranslations = app(PhpTranslations::class)->loadFromFile($outputFile);
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);

        $dir = dirname($outputFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        app(PhpTranslations::class)->saveToFile($translations, $outputFile);
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
