<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Contracts;

use DbCms\Modules\Marketing\DTOs\CreativePackage;
use DbCms\Modules\SocialPublishing\DTOs\OperationResult;
use DbCms\Modules\SocialPublishing\Models\SocialAccount;

interface SocialConnector
{
    /** @return array<string,mixed> */
    public function capabilities(): array;

    /** @param array<string,mixed> $metadata */
    public function validate(CreativePackage $package, array $metadata): void;

    public function mutation(string $step): bool;

    /**
     * @param  array<string,mixed>  $receipt
     * @param  array<string,mixed>  $metadata
     */
    public function step(string $step, SocialAccount $account, CreativePackage $package, array $receipt, array $metadata): OperationResult;

    /** @return array<string,mixed> */
    public function metrics(SocialAccount $account, string $remote): array;
}
