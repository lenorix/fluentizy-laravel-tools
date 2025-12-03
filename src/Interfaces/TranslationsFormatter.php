<?php

namespace Lenorix\FluentizyLaravelTools\Interfaces;

interface TranslationsFormatter
{
    /**
     * Load translations from a string content.
     */
    public function load(string $content): array;

    /**
     * Save translations to a string content.
     */
    public function save(array $translations): string;

    /**
     * Load translations from a file.
     */
    public function loadFromFile(string $filePath): array;

    /**
     * Save translations to a file.
     */
    public function saveToFile(array $translations, string $filePath): void;

    /**
     * Update translations file with new translations, preserving existing ones and removing obsolete ones.
     */
    public function updateTranslationsFile(string $filePath, array $newTranslations): void;

    /**
     * Update translations in a string content with new translations, preserving existing ones and removing obsolete ones.
     */
    public function updateTranslations(string $content, array $newTranslations): string;
}
