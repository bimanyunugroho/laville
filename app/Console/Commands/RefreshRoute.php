<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class RefreshRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Clear and cache routes, optimize routes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing routes...');
        Artisan::call('route:clear', [], $this->getOutput());

        $this->info('Caching routes...');
        Artisan::call('route:cache', [], $this->getOutput());

        $this->info('Optimizing...');
        Artisan::call('optimize', [], $this->getOutput());

        $this->info('✅ All steps completed successfully!');
    }
}
