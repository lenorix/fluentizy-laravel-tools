<?php

namespace Lenorix\FluentizyLaravelTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lenorix\FluentizyLaravelTools\FluentizyLaravelTools
 */
class FluentizyLaravelTools extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lenorix\FluentizyLaravelTools\FluentizyLaravelTools::class;
    }
}
