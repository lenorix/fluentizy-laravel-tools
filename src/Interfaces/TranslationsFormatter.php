<?php

namespace Lenorix\FluentizyLaravelTools\Interfaces;

interface TranslationsFormatter
{
    public function load(string $content): array;
    public function save(array $translations): string;
    public function loadFromFile(string $filePath): array;
    public function saveToFile(array $translations, string $filePath): void;
    public function updateTranslationsFile(string $filePath, array $newTranslations): void;
    public function updateTranslations(string $content, array $newTranslations): string;
}
