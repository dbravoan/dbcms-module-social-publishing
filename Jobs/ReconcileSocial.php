<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Jobs;

use DbCms\Modules\SocialPublishing\Services\DeliveryWorker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ReconcileSocial implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 90;

    public function __construct(public readonly string $deliveryId, public readonly bool $metrics = false)
    {
        $this->onConnection('marketing')->onQueue('social-reconciliation');
    }

    public function handle(DeliveryWorker $worker): void
    {
        if ($this->metrics) {
            $worker->metrics($this->deliveryId);
        } else {
            $worker->reconcile($this->deliveryId);
        }
    }
}
