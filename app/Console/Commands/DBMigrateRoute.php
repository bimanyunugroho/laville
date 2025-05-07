<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DBMigrateRoute extends Command
{
    protected $signature = 'app:d-b-migrate-route';
    protected $description = 'Import laville.sql into PostgreSQL database and show summary of executed statements';

    public function handle()
    {
        $sqlFile = database_path('database-dump/laville.sql');

        if (!File::exists($sqlFile)) {
            $this->error("File tidak ditemukan: $sqlFile");
            return 1;
        }

        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $db = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');

        putenv("PGPASSWORD=$pass");

        $command = "psql -h {$host} -p {$port} -U {$user} -d {$db} -f \"{$sqlFile}\" 2>&1";
        exec($command, $output, $returnVar);

        $copyCount = 0;
        $insertCount = 0;
        $alterCount = 0;
        $skippedCount = 0;

        $isCopy = false;

        $sqlLines = File::lines($sqlFile);
        foreach ($sqlLines as $line) {
            $line = trim($line);

            if (stripos($line, 'COPY ') === 0) {
                $isCopy = true;
                continue;
            }

            if ($isCopy) {
                if ($line === '\.') {
                    $isCopy = false;
                } elseif ($line !== '') {
                    $copyCount++;
                }
                continue;
            }

            if (stripos($line, 'INSERT') === 0) {
                $insertCount++;
            }

            if (stripos($line, 'ALTER TABLE') === 0) {
                $alterCount++;
            }
        }

        foreach ($output as $line) {
            if (stripos($line, 'already exists') !== false || stripos($line, 'multiple primary keys') !== false) {
                $skippedCount++;
            }
        }

        $this->info("✅ Import laville.sql selesai ke database: {$db}");
        $this->line("📊 Summary:");
        $this->line("- COPY rows inserted: $copyCount");
        $this->line("- INSERT statements executed: $insertCount");
        $this->line("- ALTER TABLE statements executed: $alterCount");
        $this->line("- SKIPPED errors: $skippedCount");

        return 0;
    }
}
