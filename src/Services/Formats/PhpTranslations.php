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
     * This reverses the escaping done by addcslashes() for single quotes and backslashes.
     */
    private function unescape(string $string): string
    {
        // Process the string character by character to properly handle escape sequences
        $result = '';
        $length = strlen($string);
        $i = 0;
        
        while ($i < $length) {
            if ($string[$i] === '\\' && $i + 1 < $length) {
                $nextChar = $string[$i + 1];
                // Handle escaped backslash and single quote
                if ($nextChar === '\\' || $nextChar === "'") {
                    $result .= $nextChar;
                    $i += 2;
                } else {
                    // Not an escape sequence we added, keep the backslash
                    $result .= $string[$i];
                    $i++;
                }
            } else {
                $result .= $string[$i];
                $i++;
            }
        }
        
        return $result;
    }
}
