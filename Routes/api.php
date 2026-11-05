<?php

declare(strict_types=1);
use DbCms\Core\Enums\OAuthScope;
use DbCms\Core\Http\Middleware\AuthenticateMcpPassport;
use DbCms\Modules\SocialPublishing\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateMcpPassport::class.':'.OAuthScope::SOCIAL_READ->value)->group(function (): void {
    Route::get('/publications', Api\PublicationsGetController::class);
    Route::get('/publications/{publication}', Api\PublicationResultGetController::class);
});
Route::middleware(AuthenticateMcpPassport::class.':'.OAuthScope::SOCIAL_WRITE->value)->group(function (): void {
    Route::post('/publications', Api\PublicationDraftPostController::class);
    Route::post('/publications/{publication}/schedule', Api\PublicationSchedulePostController::class);
    Route::post('/deliveries/{delivery}/cancel', Api\DeliveryCancelPostController::class);
});
