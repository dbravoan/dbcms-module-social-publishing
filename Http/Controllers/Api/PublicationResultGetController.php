<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Api;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\SocialAuthorization;
use DbCms\Modules\SocialPublishing\Services\SocialPresentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicationResultGetController extends Controller
{
    public function __invoke(Request $request, string $publication, SocialPresentation $presentation, SocialAuthorization $auth, ActorContextFactory $actors): JsonResponse
    {
        $auth->authorize($actors->fromRequest($request, 'api'), 'read:social-publishing');

        return response()->json($presentation->preview($publication));
    }
}
