<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Contracts\PublicationServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DeliveryCancelPostController extends Controller
{
    public function __invoke(Request $request, string $delivery, PublicationServiceInterface $service, ActorContextFactory $actors): RedirectResponse
    {
        $service->cancel($delivery, $actors->fromRequest($request));

        return redirect()->route('admin.social-publishing.index')->with('success', 'Cancelación registrada. Comprueba los resultados de envíos ya iniciados.');
    }
}
