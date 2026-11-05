<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\SocialPublishing\Services\SocialPresentation;
use Illuminate\Contracts\View\View;

final class PublicationPreviewGetController extends Controller
{
    public function __invoke(string $publication, SocialPresentation $presentation): View
    {
        return view('social-publishing::admin.preview', $presentation->preview($publication));
    }
}
