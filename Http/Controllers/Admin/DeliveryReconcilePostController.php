<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\DeliveryWorker;
use DbCms\Modules\SocialPublishing\Services\SocialAuthorization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DeliveryReconcilePostController extends Controller
{
    public function __invoke(Request $request, string $delivery, DeliveryWorker $worker, SocialAuthorization $auth, ActorContextFactory $actors): RedirectResponse
    {
        $auth->authorize($actors->fromRequest($request));
        $worker->reconcile($delivery);

        return back()->with('success', 'Comprobación terminada. El resultado sigue pendiente si no existe evidencia correlacionada.');
    }
}
