<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Contracts;

use DbCms\Core\Capabilities\ActorContext;
use DbCms\Modules\Marketing\DTOs\CreativePackage;

interface DestinationValidatorInterface
{
    public function check(CreativePackage $package, ?ActorContext $actor = null): void;
}
