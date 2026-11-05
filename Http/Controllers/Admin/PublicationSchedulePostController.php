<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Contracts\PublicationServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublicationSchedulePostController extends Controller
{
    public function __invoke(Request $request, string $publication, PublicationServiceInterface $service, ActorContextFactory $actors): RedirectResponse
    {
        $data = $request->validate(['publish_at' => 'required|string|max:40']);
        $service->schedule($publication, (string) $data['publish_at'], $actors->fromRequest($request));

        return redirect()->route('admin.social-publishing.preview', $publication)->with('success', 'Programación guardada.');
    }
}
