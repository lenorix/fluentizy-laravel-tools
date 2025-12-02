<?php

namespace Lenorix\FluentizyLaravelTools\Interfaces;

interface TranslationsFormatter
{
    /**
     * Load translations from a string content.
     *
     * @param string $content
     * @return array
     */
    public function load(string $content): array;

    /**
     * Save translations to a string content.
     *
     * @param array $translations
     * @return string
     */
    public function save(array $translations): string;

    /**
     * Load translations from a file.
     *
     * @param string $filePath
     * @return array
     */
    public function loadFromFile(string $filePath): array;

    /**
     * Save translations to a file.
     *
     * @param array $translations
     * @param string $filePath
     * @return void
     */
    public function saveToFile(array $translations, string $filePath): void;

    /**
     * Update translations file with new translations, preserving existing ones and removing obsolete ones.
     *
     * @param string $filePath
     * @param array $newTranslations
     * @return void
     */
    public function updateTranslationsFile(string $filePath, array $newTranslations): void;

    /**
     * Update translations in a string content with new translations, preserving existing ones and removing obsolete ones.
     *
     * @param string $content
     * @param array $newTranslations
     * @return string
     */
    public function updateTranslations(string $content, array $newTranslations): string;
}
