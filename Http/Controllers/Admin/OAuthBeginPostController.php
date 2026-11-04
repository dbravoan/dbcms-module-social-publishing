<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\SocialOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OAuthBeginPostController extends Controller
{
    public function __invoke(Request $request, string $provider, SocialOAuthService $oauth, ActorContextFactory $actors): RedirectResponse
    {
        return redirect()->away($oauth->begin($provider, $actors->fromRequest($request), $request->session()->getId()));
    }
}
