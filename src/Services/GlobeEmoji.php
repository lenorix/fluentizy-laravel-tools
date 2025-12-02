<?php

namespace Lenorix\FluentizyLaravelTools\Services;

class GlobeEmoji
{
    /**
     * Get the globe emoji for a given locale.
     *
     * @param string $locale The locale, language or country code (e.g., 'en_US', 'fr_FR', 'zh').
     * @return string The corresponding globe emoji.
     */
    public function emoji(string $locale): string
    {
        [$language, $country] = explode('_', $locale.'_');
        $language = strtolower($language);
        $country = strtolower($country);

        $globes = [
            '🌍' => [
                'en', 'de', 'fr', 'it', 'es', 'pt', 'nl', 'sv', 'no', 'da', 'fi', 'wo', 'ff', 'ts', 'sn', 'ny',
                'tr', 'pl', 'cs', 'hu', 'ro', 'sk', 'sl', 'hr', 'sr', 'bg', 'el', 'om', 'ti', 'tn', 'mg', 'ss',
                'is', 'ga', 'cy', 'eu', 'gl', 'ca', 'af', 'sw', 'zu', 'xh', 'st', 'lg', 've', 'rw', 'so', 'bm',
                'ru', 'uk', 'be', 'et', 'lv', 'lt', 'sq', 'mk', 'mt', 'ar', 'am',
            ],
            '🌎' => [
                'bn', 'ta', 'te', 'ml', 'kn', 'mr', 'qu', 'gn', 'ay', 'ha', 'br', 'ht', 'km', 'lo', 'my', 'ha',
                'gu', 'pa', 'si', 'ne', 'yo', 'ig',
            ],
            '🌏' => [
                'zh', 'ja', 'ko', 'kk', 'mn', 'vi', 'th', 'fa', 'ur', 'he', 'id', 'ty', 'mi', 'sm', 'to', 'az',
                'ms', 'tl', 'su', 'jv', 'my', 'km', 'lo', 'hi', 'mr', 'ml', 'kn', 'fj', 'ka', 'te', 'ta', 'hy',
            ],
            '🌐' => [
                'eo', 'ia',
            ],
        ];

        foreach ([$country, $language] as $code) {
            foreach ($globes as $emoji => $codes) {
                if (in_array($code, $codes)) {
                    return $emoji;
                }
            }
        }

        return '🌐';
    }
}
