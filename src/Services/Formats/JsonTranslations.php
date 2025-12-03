<?php

namespace Lenorix\FluentizyLaravelTools\Services\Formats;

use Lenorix\FluentizyLaravelTools\Interfaces\TranslationsFormatter;
use Lenorix\FluentizyLaravelTools\Traits\TranslationsFormat;

class JsonTranslations implements TranslationsFormatter
{
    use TranslationsFormat;

    public function load(string $content): array
    {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode JSON: '.json_last_error_msg());
        }

        return $decoded ?? [];
    }

    public function save(array $translations): string
    {
        return json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
