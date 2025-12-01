<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;

class FluentizyLaravelToolsCommand extends Command
{
    public $signature = 'fluentizy-laravel-tools';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
