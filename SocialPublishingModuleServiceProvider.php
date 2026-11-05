<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing;

use DbCms\Core\Capabilities\CapabilityRegistry;
use DbCms\Core\Events\ModuleAvailabilityChanged;
use DbCms\Core\Integrations\IntegrationGuideRegistry;
use DbCms\Core\Models\User;
use DbCms\Core\Modules\BaseModuleServiceProvider;
use DbCms\Core\Services\Privacy\LegalPageService;
use DbCms\Modules\SocialPublishing\Capabilities\SocialCapabilities;
use DbCms\Modules\SocialPublishing\Console\DispatchSocial;
use DbCms\Modules\SocialPublishing\Console\SocialPrivacyCommand;
use DbCms\Modules\SocialPublishing\Contracts\DestinationValidatorInterface;
use DbCms\Modules\SocialPublishing\Contracts\PublicationServiceInterface;
use DbCms\Modules\SocialPublishing\Http\Controllers\AssetGetController;
use DbCms\Modules\SocialPublishing\Models\OAuthState;
use DbCms\Modules\SocialPublishing\Models\SocialAccount;
use DbCms\Modules\SocialPublishing\Services\DeliveryWorker;
use DbCms\Modules\SocialPublishing\Services\DestinationValidator;
use DbCms\Modules\SocialPublishing\Services\PublicationService;
use DbCms\Modules\SocialPublishing\Services\SocialGuides;
use DbCms\Modules\SocialPublishing\Services\SocialPrivacyService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SocialPublishingModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $identifier = 'social-publishing';

    protected string $name = 'SocialPublishing';

    protected bool $isPageable = false;

    public function register(): void
    {
        $this->app->bind(PublicationServiceInterface::class, PublicationService::class);
        $this->app->bind(DestinationValidatorInterface::class, DestinationValidator::class);
        $this->mergeConfigFrom(__DIR__.'/config/social_publishing.php', 'social_publishing');
    }

    public function boot(): void
    {
        parent::boot();
        User::deleting(static function (User $user): void {
            if (Schema::hasTable('sp_accounts') && SocialAccount::query()->where('connected_by', $user->id)->where('status', 'connected')->exists()) {
                throw ValidationException::withMessages(['user' => 'Reassign or revoke the connected social accounts before deleting their connecting user.']);
            }
            if (Schema::hasTable('sp_oauth_states')) {
                OAuthState::query()->where('user_id', $user->id)->delete();
            }
        });

        $this->loadViewsFrom(__DIR__.'/Resources/views', 'social-publishing');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $timezone = (string) $this->getSetting('timezone', config('app.timezone', 'UTC'));
        config(['social_publishing.timezone' => in_array($timezone, \DateTimeZone::listIdentifiers(), true) ? $timezone : 'UTC']);
        $this->app->make(IntegrationGuideRegistry::class)->register('social-publishing', $this->app->make(SocialGuides::class)->all());
        $this->app->make(SocialCapabilities::class)->register($this->app->make(CapabilityRegistry::class));
        Route::prefix('api/v1/social-publishing')->middleware(['api', 'module.enabled:social-publishing'])->group(__DIR__.'/Routes/api.php');
        Route::get('/social-publishing/assets/{delivery}/{index}', AssetGetController::class)
            ->whereNumber('index')->middleware(['web', 'signed', 'module.enabled:social-publishing'])->name('social-publishing.asset');
        Event::listen(ModuleAvailabilityChanged::class, function (ModuleAvailabilityChanged $event): void {
            if ($event->module === 'social-publishing' && ! $event->available && Schema::hasTable('sp_deliveries')) {
                $this->app->make(DeliveryWorker::class)->pause();
            }
        });
        $this->commands([DispatchSocial::class, SocialPrivacyCommand::class]);
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->call(function (): void {
                if (Schema::hasTable('sp_oauth_states')) {
                    OAuthState::query()->where('expires_at', '<=', now())->delete();
                }
                if (Schema::hasTable('sp_publications') && app(LegalPageService::class)->canCollect('social-publishing')) {
                    $this->app->make(SocialPrivacyService::class)->prune();
                }
            })->daily()->name('social-publishing:privacy-retention')->withoutOverlapping();
            $id = (string) config('marketing.installation_id');
            $schedule->command('social-publishing:dispatch')->everyMinute()->name('social-publishing:dispatch:'.$id)->createMutexNameUsing('social-publishing:dispatch:'.$id)->withoutOverlapping(5)->onOneServer();
            $schedule->command('social-publishing:dispatch --metrics')->hourly()->name('social-publishing:metrics:'.$id)->createMutexNameUsing('social-publishing:metrics:'.$id)->withoutOverlapping(15)->onOneServer();
        });

    }

    public function getAdminNavigation(): array
    {
        return [
            [
                'title' => 'SocialPublishing',
                'route' => 'admin.social-publishing.index',
                'icon' => 'fa-solid fa-calendar-days',
                'permission' => 'read:social-publishing',
            ],
        ];
    }
}
