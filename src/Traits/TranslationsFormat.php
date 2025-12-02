<?php

namespace Lenorix\FluentizyLaravelTools\Traits;

trait TranslationsFormat
{
    abstract public function load(string $content): array;
    abstract public function save(array $translations): string;

    /**
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

    private function recoverPreviousTranslations(array $oldTranslations, array $newTranslations): array
    {
        $translations = [];
        foreach ($newTranslations as $key => $value) {
            $translations[$key] = $oldTranslations[$key] ?? $value;
        }

        return $translations;
    }
}
