<?php

namespace PWWEB\Artomator\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'artomator:install {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Artomator components and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'artomator', '--force' => true]);

        $this->callSilent('vendor:publish', ['--provider' => 'InfyOm\Generator\InfyOmGeneratorServiceProvider', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'lighthouse-schema', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'lighthouse-config', '--force' => true]);

        $this->callSilent('artomator:publish');
    }
}
