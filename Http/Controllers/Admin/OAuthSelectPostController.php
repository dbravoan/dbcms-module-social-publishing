<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\SocialOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OAuthSelectPostController extends Controller
{
    public function __invoke(Request $request, SocialOAuthService $oauth, ActorContextFactory $actors): RedirectResponse
    {
        $data = $request->validate(['state_id' => 'required|uuid', 'remote_id' => 'required|string|max:200']);
        $oauth->select((string) $data['state_id'], (string) $data['remote_id'], $actors->fromRequest($request), $request->session()->getId());

        return redirect()->route('admin.social-publishing.index')->with('success', 'Cuenta autorizada y seleccionada.');
    }
}
