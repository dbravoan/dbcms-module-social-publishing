<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\SocialPublishing\Services\SocialAssets;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AssetGetController extends Controller
{
    public function __invoke(string $delivery, int $index, SocialAssets $assets): BinaryFileResponse
    {
        return $assets->publicFile($delivery, $index);
    }
}
