<?php

declare(strict_types=1);

namespace DbCms\Modules\SocialPublishing\Services;

use DbCms\Core\Services\Privacy\LegalPageService;
use DbCms\Modules\Marketing\Contracts\MarketingArtifactStoreInterface;
use DbCms\Modules\SocialPublishing\Models\OAuthState;
use DbCms\Modules\SocialPublishing\Models\SocialAccount;
use DbCms\Modules\SocialPublishing\Models\SocialPublication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Local data operations never claim to erase posts held by remote providers. */
final readonly class SocialPrivacyService
{
    public function __construct(private LegalPageService $privacy, private MarketingArtifactStoreInterface $files, private SocialAuthorization $auth) {}

    /** @return array<string, mixed> */
    public function export(string $accountId): array
    {
        $account = SocialAccount::query()->whereKey($accountId)->firstOrFail();
        $publications = SocialPublication::query()->where('account_id', $accountId)->get();
        if ($publications->contains(fn (SocialPublication $publication): bool => $publication->installation_id !== $this->auth->installation())) {
            throw ValidationException::withMessages(['account' => 'Restore the owning installation context before exporting this account.']);
        }
        $ids = $publications->modelKeys();
        $deliveries = DB::table('sp_deliveries')->whereIn('publication_id', $ids)->get(['id', 'publication_id', 'revision_id', 'status', 'remote_id', 'error_code', 'created_at', 'updated_at']);

        return [
            'account' => $account->only(['id', 'provider', 'remote_id', 'identity', 'label', 'status', 'grants', 'metadata', 'connected_by', 'created_at', 'updated_at']),
            'publications' => $publications->toArray(),
            'revisions' => DB::table('sp_revisions')->whereIn('publication_id', $ids)->get()->map(static fn (object $row): array => (array) $row)->all(),
            'deliveries' => $deliveries->map(static fn (object $row): array => (array) $row)->all(),
            'remote_erasure' => 'Not performed. Review and fulfill any provider-side request separately.',
        ];
    }

    public function erase(string $accountId): void
    {
        DB::transaction(function () use ($accountId): void {
            $account = SocialAccount::query()->whereKey($accountId)->lockForUpdate()->firstOrFail();
            if (! in_array($account->status, ['revoked', 'manual_export'], true)) {
                throw ValidationException::withMessages(['account' => 'Revoke the connection before erasing its local data.']);
            }
            $publications = SocialPublication::query()->where('account_id', $accountId)->lockForUpdate()->get();
            foreach ($publications as $publication) {
                $this->erasePublication($publication);
            }
            $account->delete();
        });
    }

    /** @return array{expired_oauth:int, publications:int} */
    public function prune(): array
    {
        $expired = OAuthState::query()->where('expires_at', '<=', now())->delete();
        $policy = $this->privacy->requireProcessing('social-publishing', true);
        $days = $policy['retention_days'] ?? null;
        if (! is_int($days) || $days < 1) {
            throw ValidationException::withMessages(['retention' => 'Social publication retention requires manual review under the configured criterion.']);
        }
        $removed = 0;
        $cutoff = now()->subDays($days);
        SocialPublication::query()->where('updated_at', '<', $cutoff)->orderBy('id')->eachById(function (SocialPublication $publication) use ($cutoff, &$removed): void {
            DB::transaction(function () use ($publication, $cutoff, &$removed): void {
                $locked = SocialPublication::query()->whereKey($publication->id)->lockForUpdate()->first();
                if ($locked === null || $locked->getAttribute('updated_at') >= $cutoff) {
                    return;
                }
                $deliveries = DB::table('sp_deliveries')->where('publication_id', $locked->id)->lockForUpdate()->get();
                if ($locked->revision_id === null || ! $deliveries->contains(fn (object $delivery): bool => $delivery->revision_id === $locked->revision_id) || $deliveries->isEmpty() || $deliveries->contains(static fn (object $delivery): bool => ! in_array($delivery->status, ['published', 'cancelled', 'failed'], true) || $delivery->lease_until !== null || $delivery->updated_at >= $cutoff->toDateTimeString())) {
                    return;
                }
                $this->erasePublication($locked);
                $removed++;
            });
        });

        return ['expired_oauth' => $expired, 'publications' => $removed];
    }

    public function cleanupArtifacts(string $publicationId): void
    {
        if (! Str::isUuid($publicationId) || SocialPublication::query()->whereKey($publicationId)->exists()) {
            throw ValidationException::withMessages(['publication' => 'Only artifacts of a verified, removed publication may be cleaned.']);
        }
        $installation = $this->auth->installation();
        $this->files->deleteOwner('social/'.$installation.'/'.$publicationId);
    }

    private function erasePublication(SocialPublication $publication): void
    {
        if ($publication->installation_id !== $this->auth->installation()) {
            throw ValidationException::withMessages(['publication' => 'This publication belongs to a different installation.']);
        }
        $deliveries = DB::table('sp_deliveries')->where('publication_id', $publication->id)->lockForUpdate()->get();
        if ($deliveries->contains(static fn (object $delivery): bool => ! in_array($delivery->status, ['published', 'cancelled', 'failed'], true) || $delivery->lease_until !== null)) {
            throw ValidationException::withMessages(['publication' => 'Resolve or cancel every pending, uncertain or running delivery before erasing its evidence.']);
        }
        $deliveryIds = $deliveries->pluck('id')->all();
        if (DB::table('sp_attempts')->whereIn('delivery_id', $deliveryIds)->where('status', 'started')->lockForUpdate()->exists()) {
            throw ValidationException::withMessages(['publication' => 'Resolve outstanding provider attempts before erasing delivery evidence.']);
        }
        $revisionIds = DB::table('sp_revisions')->where('publication_id', $publication->id)->pluck('id')->all();
        DB::table('sp_metrics')->whereIn('delivery_id', $deliveryIds)->delete();
        DB::table('sp_attempts')->whereIn('delivery_id', $deliveryIds)->delete();
        DB::table('sp_deliveries')->whereIn('id', $deliveryIds)->delete();
        DB::table('sp_approvals')->whereIn('revision_id', $revisionIds)->delete();
        DB::table('sp_revisions')->whereIn('id', $revisionIds)->delete();
        $id = $publication->id;
        $publication->delete();
        DB::afterCommit(fn () => $this->cleanupArtifacts($id));
    }
}
