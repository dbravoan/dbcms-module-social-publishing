<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Console;

use DbCms\Modules\SocialPublishing\Services\SocialPrivacyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SocialPrivacyCommand extends Command
{
    protected $signature = 'social-publishing:privacy {action : export, erase-local, prune or cleanup-artifacts} {--id= : Explicit account or removed-publication UUID} {--verified : Request scope and retention restrictions were checked}';

    protected $description = 'Manage local social data; remote provider erasure remains a separate operation';

    public function handle(SocialPrivacyService $privacy): int
    {
        $action = $this->argument('action');
        if ($action === 'prune') {
            $this->line(json_encode($privacy->prune(), JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }
        $id = $this->option('id');
        if (! $this->option('verified') || ! is_string($id) || ! Str::isUuid($id) || ! in_array($action, ['export', 'erase-local', 'cleanup-artifacts'], true)) {
            $this->error('Choose an action with an explicit UUID and --verified after reviewing scope and retention.');

            return self::FAILURE;
        }
        if ($action === 'cleanup-artifacts') {
            $privacy->cleanupArtifacts($id);
        } elseif ($action === 'erase-local') {
            $privacy->erase($id);
            $this->warn('Local data erased. Provider-side posts and credentials must be reviewed separately.');
        } else {
            $path = 'privacy/social-'.Str::uuid().'.json';
            Storage::disk('local')->put($path, json_encode($privacy->export($id), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), 'private');
            $this->info('Private export: '.Storage::disk('local')->path($path).'. Delete it after fulfillment.');
        }

        return self::SUCCESS;
    }
}
