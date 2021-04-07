<?php

namespace PWWEB\Artomator\Commands\Scaffold;

use InfyOm\Generator\Commands\Scaffold\ViewsGeneratorCommand as Base;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\Scaffold\ViewGenerator;
use PWWEB\Artomator\Generators\Scaffold\VueGenerator;
use Symfony\Component\Console\Input\InputOption;

class ViewsGeneratorCommand extends Base
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.scaffold:views';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->commandData->modelName = $this->argument('model');

        $this->commandData->initCommandData();
        $this->commandData->getFields();

        if (false === $this->commandData->getOption('vue')) {
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->generate();
        } else {
            $vueGenerator = new VueGenerator($this->commandData);
            $vueGenerator->generate();
        }

        $this->performPostActions();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['vue', null, InputOption::VALUE_NONE, 'Generate Vuejs views rather than blade views'],
            ]
        );
    }
}
