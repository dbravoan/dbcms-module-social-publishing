<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Contracts\PublicationServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublicationApprovePostController extends Controller
{
    public function __invoke(Request $request, string $publication, PublicationServiceInterface $service, ActorContextFactory $actors): RedirectResponse
    {
        $data = $request->validate(['payload_hash' => 'required|string|size:64', 'brand_checked' => 'accepted', 'rights_checked' => 'accepted', 'publish_at' => 'nullable|string|max:40']);
        $actor = $actors->fromRequest($request);
        $p = isset($data['publish_at']) ? $service->approveAndSchedule($publication, (string) $data['payload_hash'], (string) $data['publish_at'], $actor) : $service->approve($publication, (string) $data['payload_hash'], $actor);

        return redirect()->route('admin.social-publishing.preview', $p->id)->with('success', 'Revisión aprobada.');
    }
}
