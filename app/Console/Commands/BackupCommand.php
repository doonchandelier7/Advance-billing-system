<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupCommand extends Command
{
    protected $signature = 'app:backup {--db : Dump database} {--storage= : Copy storage path}';
    protected $description = 'Backup database and/or storage (foundation for backup & restore).';

    public function handle(): int
    {
        $backupDir = 'backups/' . date('Y-m-d_His');
        if (! Storage::exists($backupDir)) {
            Storage::makeDirectory($backupDir);
        }

        if ($this->option('db')) {
            $this->backupDatabase($backupDir);
        }

        if ($path = $this->option('storage')) {
            $this->backupStorage($backupDir, $path);
        }

        if (! $this->option('db') && ! $this->option('storage')) {
            $this->backupDatabase($backupDir);
            $this->info('Database backup created in storage/app/' . $backupDir);
            $this->info('Use --storage=invoice-uploads to include uploads.');
        }

        return self::SUCCESS;
    }

    protected function backupDatabase(string $backupDir): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver !== 'mysql') {
            $this->warn('DB backup supported for MySQL. Export manually for other drivers.');
            return;
        }

        $db = config("database.connections.{$connection}.database");
        $user = config("database.connections.{$connection}.username");
        $pass = config("database.connections.{$connection}.password");
        $host = config("database.connections.{$connection}.host");
        $filename = $backupDir . '/database.sql';

        $cmd = sprintf(
            'mysqldump -h %s -u %s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg(Storage::path($filename))
        );

        exec($cmd, $out, $code);
        if ($code === 0) {
            $this->info('Database dumped to ' . $filename);
        } else {
            $this->error('Database dump failed. Install mysqldump or run manually.');
        }
    }

    protected function backupStorage(string $backupDir, string $path): void
    {
        $from = storage_path('app/' . $path);
        if (! is_dir($from)) {
            $this->warn("Storage path not found: {$from}");
            return;
        }
        $dest = Storage::path($backupDir . '/' . basename($path));
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $this->copyDir($from, $dest);
        $this->info('Storage copied to ' . $backupDir . '/' . basename($path));
    }

    protected function copyDir(string $src, string $dest): void
    {
        $dir = opendir($src);
        while (($f = readdir($dir)) !== false) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $srcPath = $src . '/' . $f;
            $destPath = $dest . '/' . $f;
            if (is_dir($srcPath)) {
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $this->copyDir($srcPath, $destPath);
            } else {
                copy($srcPath, $destPath);
            }
        }
        closedir($dir);
    }
}
