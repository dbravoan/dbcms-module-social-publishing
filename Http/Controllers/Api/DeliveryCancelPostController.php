<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Api;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Contracts\PublicationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeliveryCancelPostController extends Controller
{
    public function __invoke(Request $request, string $delivery, PublicationServiceInterface $service, ActorContextFactory $actors): JsonResponse
    {
        $service->cancel($delivery, $actors->fromRequest($request, 'api'));

        return response()->json(['cancellation_requested' => true], 202);
    }
}
