<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Services;

use Symfony\Component\Process\Process;

/**
 * Thin wrapper around the three host scripts:
 *   - /usr/local/bin/site-backup.sh         (create backup)
 *   - /usr/local/bin/site-backup-list.sh    (list)
 *   - /usr/local/bin/site-restore.sh        (restore w/ dir swap)
 *
 * Invoked via `sudo -n` — a NOPASSWD sudoers entry must allow the running
 * user (deploy) to execute exactly these 3 binaries.
 *
 * Site key (casinoreview/casinobonusnews/tragamonedas) is resolved from
 * the host's DB_DATABASE — each Laravel install has its own DB named after
 * its site key, matching the SITES array in the shell scripts.
 */
class BackupShell
{
    private const TIMEOUT_CREATE = 600;   // 10 min  (db dump + tar.zst + ftp upload)

    private const TIMEOUT_LIST = 60;

    private const TIMEOUT_RESTORE = 900;  // 15 min (download + extract + pg_restore + swap)

    /**
     * Map the current host to the site key used by the shell scripts:
     * derived from APP_URL host, since DB names don't always match
     * (casinoreview's DB is "gamble", but the script's key is "casinoreview").
     */
    public function siteKey(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: '';

        return match (true) {
            str_contains($host, 'casinoreviewru') => 'casinoreview',
            str_contains($host, 'casinobonusnews') => 'casinobonusnews',
            str_contains($host, 'tragamonedas') => 'tragamonedas',
            default => $host,
        };
    }

    /** @return array<string, mixed> */
    public function list(): array
    {
        $process = new Process(
            ['sudo', '-n', '/usr/local/bin/site-backup-list.sh', '--site='.$this->siteKey()],
            timeout: self::TIMEOUT_LIST,
        );
        $process->run();
        if (! $process->isSuccessful()) {
            return ['error' => trim($process->getErrorOutput() ?: 'list failed'), 'site' => $this->siteKey(), 'backups' => []];
        }

        $json = trim($process->getOutput());
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : ['error' => 'invalid script output', 'raw' => $json];
    }

    /** @return array<string, mixed> */
    public function create(): array
    {
        $process = new Process(
            ['sudo', '-n', '/usr/local/bin/site-backup.sh', '--site='.$this->siteKey()],
            timeout: self::TIMEOUT_CREATE,
        );
        $process->run();

        return [
            'status' => $process->isSuccessful() ? 'ok' : 'failed',
            'exit_code' => $process->getExitCode(),
            'log_tail' => $this->tail($process->getOutput().$process->getErrorOutput(), 40),
        ];
    }

    /** @return array<string, mixed> */
    public function restore(string $date, string $requestId): array
    {
        $process = new Process(
            ['sudo', '-n', '/usr/local/bin/site-restore.sh',
                '--site='.$this->siteKey(),
                '--date='.$date,
                '--request-id='.$requestId,
            ],
            timeout: self::TIMEOUT_RESTORE,
        );
        $process->run();

        $out = trim($process->getOutput());
        $decoded = json_decode($out, true);

        if (is_array($decoded)) {
            $decoded['exit_code'] = $process->getExitCode();
            $decoded['log_tail'] = $this->tail($process->getErrorOutput(), 40);

            return $decoded;
        }

        return [
            'status' => 'failed',
            'exit_code' => $process->getExitCode(),
            'log_tail' => $this->tail($process->getOutput().$process->getErrorOutput(), 80),
        ];
    }

    private function tail(string $text, int $lines): string
    {
        $arr = explode("\n", trim($text));

        return implode("\n", array_slice($arr, -$lines));
    }
}
