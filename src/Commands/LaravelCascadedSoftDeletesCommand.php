<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Commands;

use Illuminate\Console\Command;

class LaravelCascadedSoftDeletesCommand extends Command
{
    public $signature = 'laravel-cascaded-soft-deletes';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
