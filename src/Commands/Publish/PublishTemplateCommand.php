<?php

namespace PWWEB\Artomator\Commands\Publish;

use InfyOm\Generator\Commands\Publish\PublishBaseCommand;

class PublishTemplateCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator.publish:templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes artomator generator templates.';

    private $templatesDir;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->templatesDir = config(
            'infyom.laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
        );

        if ($this->publishGeneratorTemplates()) {
            $this->publishScaffoldTemplates();
            $this->publishSwaggerTemplates();
        }
    }

    /**
     * Publishes templates.
     */
    public function publishGeneratorTemplates()
    {
        $templatesPath = __DIR__.'/../../../templates';

        $this->publishDirectory($templatesPath, $this->templatesDir, 'pwweb-artomator-templates');

        $templatesPath = __DIR__.'/../../../../../infyomlabs/templates';

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'infyom-generator-templates');
    }

    /**
     * Publishes scaffold stemplates.
     */
    public function publishScaffoldTemplates()
    {
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $templatesPath = base_path('vendor/infyomlabs/'.$templateType.'/templates/scaffold');

        return $this->publishDirectory($templatesPath, $this->templatesDir.'scaffold', 'infyom-generator-templates/scaffold', true);
    }

    /**
     * Publishes swagger stemplates.
     */
    public function publishSwaggerTemplates()
    {
        $templatesPath = base_path('vendor/infyomlabs/swagger-generator/templates');

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'swagger-generator', true);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}