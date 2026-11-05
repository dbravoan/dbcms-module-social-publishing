<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Console;

use DbCms\Modules\SocialPublishing\Services\DeliveryDispatcher;
use Illuminate\Console\Command;

final class DispatchSocial extends Command
{
    protected $signature = 'social-publishing:dispatch {--metrics}';

    protected $description = 'Envía referencias de trabajo duradero; los resultados inciertos nunca se reenvían.';

    public function handle(DeliveryDispatcher $dispatcher): int
    {
        $this->info((string) $dispatcher->dispatch((bool) $this->option('metrics')));

        return self::SUCCESS;
    }
}
