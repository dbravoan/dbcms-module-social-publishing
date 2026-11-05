<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Capabilities\ActorContextFactory;
use DbCms\Modules\SocialPublishing\Services\SocialOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AccountRevokePostController extends Controller
{
    public function __invoke(Request $request, string $account, SocialOAuthService $oauth, ActorContextFactory $actors): RedirectResponse
    {
        $oauth->revoke($account, $actors->fromRequest($request));

        return back()->with('success', 'Conexión local revocada y credenciales eliminadas.');
    }
}
