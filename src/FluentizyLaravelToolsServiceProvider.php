<?php

namespace Lenorix\FluentizyLaravelTools;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Lenorix\FluentizyLaravelTools\Commands\FluentizyLaravelToolsCommand;

class FluentizyLaravelToolsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fluentizy-laravel-tools')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_fluentizy_laravel_tools_table')
            ->hasCommand(FluentizyLaravelToolsCommand::class);
    }
}
