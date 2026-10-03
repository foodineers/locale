<?php

declare(strict_types=1);

namespace Foodineers\Locale\Commands;

use Illuminate\Console\Command;
use Override;

final class LocaleCommand extends Command
{
    #[Override]
    public $signature = 'locale';

    #[Override]
    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
