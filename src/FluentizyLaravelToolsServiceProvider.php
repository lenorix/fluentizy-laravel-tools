<?php

namespace Lenorix\FluentizyLaravelTools;

use Lenorix\FluentizyLaravelTools\Commands\LangExtractCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->name('fluentizy-tools')
            // ->hasConfigFile()
            // ->hasViews()
            // ->hasMigration('create_fluentizy_laravel_tools_table')
            ->hasTranslations()
            ->hasCommand(LangExtractCommand::class);
    }
}
