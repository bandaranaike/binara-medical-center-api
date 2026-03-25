<?php

namespace App\Console\Commands;

use App\Models\PublicAppToken;
use App\Models\TrustedSite;
use Illuminate\Console\Command;

class CreatePublicApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'public-api:token
        {trusted_site : Trusted site ID or domain}
        {name : Human-readable token name}
        {--expires-at= : Optional expiration timestamp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a public API token for a trusted site';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $trustedSiteInput = (string) $this->argument('trusted_site');

        $trustedSite = TrustedSite::query()
            ->when(
                ctype_digit($trustedSiteInput),
                fn ($query) => $query->whereKey((int) $trustedSiteInput),
                fn ($query) => $query->where('domain', $trustedSiteInput)
            )
            ->first();

        if (! $trustedSite) {
            $this->error('Trusted site not found.');

            return self::FAILURE;
        }

        [$token, $plainTextToken] = PublicAppToken::issueForTrustedSite(
            $trustedSite,
            (string) $this->argument('name'),
            ['*'],
            $this->option('expires-at') ?: null,
        );

        $this->info('Public API token created successfully.');
        $this->line('Trusted site: '.$trustedSite->domain);
        $this->line('Token name: '.$token->name);
        $this->line('Token: '.$plainTextToken);

        return self::SUCCESS;
    }
}
