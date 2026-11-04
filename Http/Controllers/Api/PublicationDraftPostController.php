<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Api;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Http\Requests\DraftRequest;
use DbCms\Modules\SocialPublishing\Services\PublicationInputService;
use Illuminate\Http\JsonResponse;

final class PublicationDraftPostController extends Controller
{
    public function __invoke(DraftRequest $request, PublicationInputService $input, ActorContextFactory $actors): JsonResponse
    {
        $p = $input->create($request->payload(), array_values((array) $request->file('files', [])), $actors->fromRequest($request, 'api'));

        return response()->json(['publication' => $p], 201);
    }
}
