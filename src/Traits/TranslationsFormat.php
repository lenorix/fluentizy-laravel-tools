<?php

namespace Lenorix\FluentizyLaravelTools\Traits;

/**
 * Trait for translation file formats common functionality.
 */
trait TranslationsFormat
{
    /**
     * Load translations from a string content.
     */
    abstract public function load(string $content): array;

    /**
     * Save translations to a string content.
     */
    abstract public function save(array $translations): string;

    /**
     * Load translations from a file.
     *
     * @throws \Exception When file read/write fails
     */
    public function loadFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }

        return $this->load($content);
    }

    /**
     * Save translations to a file.
     *
     * @throws \Exception When file read/write fails
     */
    public function saveToFile(array $translations, string $filePath): void
    {
        $content = $this->save($translations);
        $result = file_put_contents($filePath, $content);
        if ($result === false) {
            throw new \Exception("Failed to write to file: {$filePath}");
        }
    }

    /**
     * Update translations file with new translations, preserving existing ones and removing obsolete ones.
     *
     * @throws \Exception When file read/write fails
     */
    public function updateTranslationsFile(string $filePath, array $newTranslations): void
    {
        $oldTranslations = [];
        if (file_exists($filePath)) {
            $oldTranslations = $this->loadFromFile($filePath);
        }
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);

        $dir = dirname($filePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->saveToFile($translations, $filePath);
    }

    /**
     * Update translations in string with new translations, preserving existing ones and removing obsolete ones.
     */
    public function updateTranslations(string $content, array $newTranslations): string
    {
        $oldTranslations = $this->load($content);
        $translations = $this->recoverPreviousTranslations($oldTranslations, $newTranslations);

        return $this->save($translations);
    }

    private function recoverPreviousTranslations(array $oldTranslations, array $newTranslations): array
    {
        $translations = [];
        foreach ($newTranslations as $key => $value) {
            $translations[$key] = $oldTranslations[$key] ?? $value;
        }

        return $translations;
    }
}
