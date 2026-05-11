<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Console\Commands;

use App\Models\User;
use CoolMacJedi\LaravelCmsApi\Enums\ApiAbility;
use Illuminate\Console\Command;

/**
 * Issue a Sanctum personal access token with one or more API abilities.
 *
 * Examples:
 *   php artisan api:token-issue admin@example.com --name="local-agent" \
 *       --abilities=content:read,content:write,media:upload
 *
 *   php artisan api:token-issue admin@example.com --name="full" --all
 *
 * The plaintext token is printed once and never again — store it immediately.
 *
 * Hard-coupled to App\Models\User (must use HasApiTokens trait).
 */
final class IssueToken extends Command
{
    protected $signature = 'api:token-issue
        {email : Email of the user the token is issued to}
        {--name= : Human-readable token label}
        {--abilities= : Comma-separated abilities}
        {--all : Grant all abilities}';

    protected $description = 'Issue a scoped Sanctum API token for the CMS v1 API.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error("User not found: {$this->argument('email')}");

            return self::FAILURE;
        }

        $name = (string) ($this->option('name') ?: 'agent-'.now()->format('YmdHis'));

        $abilities = $this->resolveAbilities();
        if ($abilities === null) {
            return self::FAILURE;
        }

        $token = $user->createToken($name, $abilities);

        $this->newLine();
        $this->info('Token issued.');
        $this->line('  user      : '.$user->email);
        $this->line('  name      : '.$name);
        $this->line('  abilities : '.implode(', ', $abilities));
        $this->newLine();
        $this->line('  Plaintext (store NOW, shown once):');
        $this->line('  '.$token->plainTextToken);
        $this->newLine();

        return self::SUCCESS;
    }

    /** @return list<string>|null */
    private function resolveAbilities(): ?array
    {
        if ($this->option('all')) {
            return ApiAbility::values();
        }

        $raw = $this->option('abilities');
        if (! $raw) {
            $this->error('--abilities is required (comma-separated). Use --all to grant everything.');

            return null;
        }

        $known = ApiAbility::values();
        $requested = array_map('trim', explode(',', (string) $raw));
        $unknown = array_diff($requested, $known);

        if ($unknown !== []) {
            $this->error('Unknown abilities: '.implode(', ', $unknown));
            $this->line('Allowed: '.implode(', ', $known));

            return null;
        }

        return array_values($requested);
    }
}
