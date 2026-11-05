<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Services;

use DbCms\Modules\SocialPublishing\Connectors\FacebookConnector;
use DbCms\Modules\SocialPublishing\Connectors\InstagramConnector;
use DbCms\Modules\SocialPublishing\Connectors\LinkedInConnector;
use DbCms\Modules\SocialPublishing\Connectors\ManualConnector;
use DbCms\Modules\SocialPublishing\Connectors\YouTubeConnector;
use DbCms\Modules\SocialPublishing\Contracts\SocialConnector;

final class ConnectorRegistry
{
    public function get(string $provider): SocialConnector
    {
        return match ($provider) {
            'facebook' => app(FacebookConnector::class), 'instagram' => app(InstagramConnector::class),
            'linkedin' => app(LinkedInConnector::class), 'youtube' => app(YouTubeConnector::class), 'tiktok' => app(ManualConnector::class),
            default => throw new \InvalidArgumentException('Red social no compatible.'),
        };
    }
}
