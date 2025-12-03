<?php

namespace Lenorix\FluentizyLaravelTools\Services\Formats;

use Lenorix\FluentizyLaravelTools\Interfaces\TranslationsFormatter;
use Lenorix\FluentizyLaravelTools\Traits\TranslationsFormat;

class PhpTranslations implements TranslationsFormatter
{
    use TranslationsFormat;

    public function load(string $content): array
    {
        preg_match_all("/'((?:\\\\'|[^'])*)'\s*=>\s*'((?:\\\\'|[^'])*)'/", $content, $matches, PREG_SET_ORDER);

        $translations = [];
        foreach ($matches as $match) {
            $key = $this->unescape($match[1]);
            $value = $this->unescape($match[2]);
            $translations[$key] = $value;
        }

        return $translations;
    }

    public function save(array $translations): string
    {
        $content = "<?php\n\nreturn [";
        foreach ($translations as $key => $value) {
            $escapedKey = $this->escape($key);
            $escapedValue = $this->escape($value);
            $content .= "\n    '{$escapedKey}' => '{$escapedValue}',";
        }
        $content .= "\n];\n";

        return $content;
    }

    /**
     * Escape single quotes in a string for PHP array syntax.
     */
    private function escape(string $string): string
    {
        $string = str_replace("'", "\\'", $string);
        $string = str_replace("\n", '\\n', $string);
        $string = str_replace("\r", '\\r', $string);
        $string = str_replace("\t", '\\t', $string);
        $string = str_replace('$', '\\$', $string);
        $string = str_replace("\0", '\\0', $string);

        return str_replace('\\', '\\\\', $string);
    }

    /**
     * Unescape single quotes in a string from PHP array syntax.
     */
    private function unescape(string $string): string
    {
        $string = str_replace("\\'", "'", $string);
        $string = str_replace('\\n', "\n", $string);
        $string = str_replace('\\r', "\r", $string);
        $string = str_replace('\\t', "\t", $string);
        $string = str_replace('\\$', '$', $string);
        $string = str_replace('\\0', "\0", $string);

        return str_replace('\\\\', '\\', $string);
    }
}
