<?php

namespace Lenorix\FluentizyLaravelTools\Commands;

use Illuminate\Console\Command;

class LangExtractCommand extends Command
{
    public $signature = 'lang:extract';

    public $description = 'Extract translation strings to lang files';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
