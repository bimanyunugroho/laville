<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RefreshSetup extends Command
{
    protected $signature = 'app:refresh-setup';
    protected $description = 'Refresh migrations, clear and cache routes, optimize routes, seed database, and run dev build.';

    public function handle(): void
    {
        $this->info('Refreshing database...');
        Artisan::call('migrate:refresh', [], $this->getOutput());

        $this->info('Clearing routes...');
        Artisan::call('route:clear', [], $this->getOutput());

        $this->info('Caching routes...');
        Artisan::call('route:cache', [], $this->getOutput());

        $this->info('Optimizing...');
        Artisan::call('optimize', [], $this->getOutput());

        $this->info('Seeding database...');
        Artisan::call('db:seed', [], $this->getOutput());

        $this->info('Running composer dev build...');
        $process = Process::fromShellCommandline('composer run dev');
        $process->setTimeout(null); // Optional: disable timeout
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('✅ All steps completed successfully!');
    }
}
