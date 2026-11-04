<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Http\Requests\DraftRequest;
use DbCms\Modules\SocialPublishing\Services\PublicationInputService;
use Illuminate\Http\RedirectResponse;

final class PublicationPostController extends Controller
{
    public function __invoke(DraftRequest $request, PublicationInputService $service, ActorContextFactory $actors): RedirectResponse
    {
        $p = $service->create($request->payload(), array_values((array) $request->file('files', [])), $actors->fromRequest($request));

        return redirect()->route('admin.social-publishing.preview', $p->id)->with('success', 'Borrador guardado. Revisa esta versión exacta.');
    }
}
