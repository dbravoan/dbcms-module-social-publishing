<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Jobs;

use DbCms\Modules\SocialPublishing\Services\DeliveryWorker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class DeliverSocial implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public readonly string $deliveryId)
    {
        $this->onConnection('marketing')->onQueue('social-delivery');
    }

    public function handle(DeliveryWorker $worker): void
    {
        $worker->run($this->deliveryId);
    }
}
