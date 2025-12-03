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
     * Escape special characters in a string for PHP array syntax.
     */
    private function escape(string $string): string
    {
        return addcslashes($string, "'\\");
    }

    /**
     * Unescape special characters in a string from PHP array syntax.
     */
    private function unescape(string $string): string
    {
        return stripcslashes($string);
    }
}
