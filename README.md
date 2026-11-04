# SocialPublishing

`dbravoan/dbcms-module-social-publishing` provides owned account connections,
reviewable publications, dispatch and reconciliation. Composer requires Marketing.
Install/activate the modules, configure each network's app/account permissions
and use the module's backoffice integration guides for the selected provider.

Authorize accounts through their normal OAuth flow, review the target account
and content, then approve/schedule publication. Credentials and provider receipts
are private. Reauthorizing an account or changing credentials is not proof that
publishing works; verify the provider response and final remote result.

The scheduler runs `social-publishing:dispatch` every minute and the
`--metrics` variant hourly. Configure the Marketing delivery/reconciliation
workers, timeout and shared mutex store. Do not run a second duplicate scheduler.

An interrupted send may already have succeeded remotely. Preserve its intent and
receipt; reconcile uncertain/pending-confirmation states instead of blindly
retrying. Revoked accounts, unavailable owners and changed permissions must stop
subsequent execution. Cancellation does not undo remote publication.


New connections, publication inputs and remote processing require a reviewed
`legal.processing` entry owned by `social-publishing`, describing real data,
recipients/transfers and retention. Remote work rechecks the current policy;
local revocation and data-right commands remain available when inactive.

`social-publishing:privacy export --id=<account-uuid> --verified` writes a private
export without access/refresh tokens or private provider receipts. Delete that
export after fulfillment. `erase-local` with the same options requires a revoked
or manual account and refuses pending, uncertain or leased deliveries. It erases
only local data; provider-side removal remains an explicit separate action.

`social-publishing:privacy prune` expires OAuth states and terminal publication
histories under the configured positive `retention_days`; manual criteria require
operator review. Active/uncertain deliveries and draft histories are retained.
Cleanup of private publication artifacts follows commit. If cleanup fails, retry
`cleanup-artifacts --id=<removed-publication-uuid> --verified`; an existing
publication or unsafe storage link is refused. Connected-account user references
must be reassigned or revoked before deleting their connecting user.
