<?php

namespace Lenorix\FluentizyLaravelTools\Services\Formats;

use Lenorix\FluentizyLaravelTools\Interfaces\TranslationsFormatter;
use Lenorix\FluentizyLaravelTools\Traits\TranslationsFormat;

class JsonTranslations implements TranslationsFormatter
{
    use TranslationsFormat;

    public function load(string $content): array
    {
        $translations = json_decode($content, true);
        if (!is_array($translations)) {
            throw new \Exception('Invalid JSON translations format');
        }
        return $translations;
    }

    public function save(array $translations): string
    {
        return json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
