<?php

namespace Lenorix\FluentizyLaravelTools\Traits;

trait TranslationsFormat
{
    abstract public function load(string $content): array;
    abstract public function save(array $translations): string;

    public function loadFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read file: {$filePath}");
        }
        return $this->load($content);
    }

    public function saveToFile(array $translations, string $filePath): void
    {
        $content = $this->save($translations);
        $result = file_put_contents($filePath, $content);
        if ($result === false) {
            throw new \Exception("Failed to write to file: {$filePath}");
        }
    }
}
