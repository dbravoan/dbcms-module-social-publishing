<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\SocialAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class PublicationAssetGetController extends Controller
{
    public function __invoke(Request $request, string $publication, int $index, SocialAssets $assets, ActorContextFactory $actors): BinaryFileResponse
    {
        return $assets->preview($publication, $index, $actors->fromRequest($request));
    }
}
