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

    /** Match the DB name to the site key used by the shell scripts. */
    public function siteKey(): string
    {
        // The 3 sites use DB names that match the shell-script site keys.
        return (string) config('database.connections.'.config('database.default').'.database');
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
