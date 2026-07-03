<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class ApiConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure API token to create company.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apiKey = env('API_KEY', Str::random(100));

        Artisan::call('set:env api_key "'.$apiKey.'"');
        Artisan::call('set:env use_protection_api_key false');
        Artisan::call('config:cache');
        Artisan::call('cache:clear');

        $this->info("API key: $apiKey");
    }
}
