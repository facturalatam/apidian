<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:env {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set an env variable';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $key = strtoupper($this->argument('key'));
        $value = $this->argument('value');

        $path = base_path('.env');

        if (File::exists($path)) {
            $env = File::get($path);

            $lines = explode(PHP_EOL, $env);

            $keyFound = false;

            foreach ($lines as $index => $line) {
                $parts = explode('=', $line, 2);
                if (trim($parts[0]) === $key) {
                    $lines[$index] = $key . '=' . $value;
                    $keyFound = true;
                }
            }

            if (!$keyFound) {
                $lines[] = $key . '=' . $value;
            }

            $env = implode(PHP_EOL, $lines);

            File::put($path, $env);
        }

        return 0;
    }
}