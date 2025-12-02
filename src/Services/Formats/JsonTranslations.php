<?php

namespace Lenorix\FluentizyLaravelTools\Services\Formats;

class JsonTranslations
{
    use TranslationsFormat;

    public function load(string $content): array
    {
        return json_decode($content, true);
    }

    public function save(array $translations): string
    {
        return json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
