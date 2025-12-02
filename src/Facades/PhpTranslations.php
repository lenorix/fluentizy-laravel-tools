<?php

namespace Lenorix\FluentizyLaravelTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lenorix\FluentizyLaravelTools\Services\Formats\PhpTranslations
 */
class PhpTranslations extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lenorix\FluentizyLaravelTools\Services\Formats\PhpTranslations::class;
    }
}
