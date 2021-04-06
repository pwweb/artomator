<?php

namespace PWWEB\Artomator\Commands\Publish;

use InfyOm\Generator\Commands\Publish\PublishBaseCommand;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputOption;

class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * ALL REFERENCES TO (get_template\(\')([a-z_\.]+)(', 'laravel-generator'\))
     * REPLACED WITH: get_artomator_template('$2').
     */

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'artomator:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes & init api routes, base controller, base test cases traits.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->publishTestCases();
        $this->publishBaseController();
        $repositoryPattern = config('infyom.laravel_generator.options.repository_pattern', true);
        if (true === $repositoryPattern) {
            $this->publishBaseRepository();
        }
        if (true === $this->option('localized')) {
            $this->publishLocaleFiles();
        }
    }

    /**
     * Replaces dynamic variables of template.
     * THIS IS A NEW FUNCTION ADDED.
     *
     * @param string $templateData Template Data.
     *
     * @return string
     */
    private function fillLicense($templateData)
    {
        $replacements = [
            '$LICENSE_PACKAGE$' => config('pwweb.artomator.license.package', 'boo'),
            '$LICENSE_AUTHORS$' => license_authors(config('pwweb.artomator.license.authors')),
            '$LICENSE_COPYRIGHT$' => config('pwweb.artomator.license.copyright'),
            '$LICENSE$' => config('pwweb.artomator.license.license'),
        ];
        foreach ($replacements as $key => $replacement) {
            $templateData = str_replace($key, $replacement, $templateData);
        }

        return $templateData;
    }

    /**
     * Replaces dynamic variables of template.
     *
     * @param string $templateData Template Data.
     *
     * @return string
     */
    protected function fillTemplate($templateData)
    {
        $apiVersion = config('infyom.laravel_generator.api_version', 'v1');
        $apiPrefix = config('infyom.laravel_generator.api_prefix', 'api');

        $templateData = str_replace('$API_VERSION$', $apiVersion, $templateData);
        $templateData = str_replace('$API_PREFIX$', $apiPrefix, $templateData);
        $appNamespace = $this->getLaravel()->getNamespace();
        $appNamespace = substr($appNamespace, 0, (strlen($appNamespace) - 1));
        $templateData = str_replace('$NAMESPACE_APP$', $appNamespace, $templateData);

        // return $templateData;
        // ADDED THE FOLLOWING LINE:
        return $this->fillLicense($templateData);
    }

    /**
     * Publish Test Cases.
     *
     * @return void
     */
    private function publishTestCases()
    {
        $testsPath = config('infyom.laravel_generator.path.tests', base_path('tests/'));
        $testsNameSpace = config('infyom.laravel_generator.namespace.tests', 'Tests');
        $createdAtField = config('infyom.laravel_generator.timestamps.created_at', 'created_at');
        $updatedAtField = config('infyom.laravel_generator.timestamps.updated_at', 'updated_at');

        $templateData = get_artomator_template('test.api_test_trait');

        $templateData = str_replace('$NAMESPACE_TESTS$', $testsNameSpace, $templateData);
        $templateData = str_replace('$TIMESTAMPS$', "['$createdAtField', '$updatedAtField']", $templateData);

        // ADDED THE FOLLOWING LINE:
        $templateData = $this->fillLicense($templateData);

        $fileName = 'ApiTestTrait.php';

        if (true === file_exists($testsPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($testsPath, $fileName, $templateData);
        $this->info('ApiTestTrait created');

        $testAPIsPath = config('infyom.laravel_generator.path.api_test', base_path('tests/APIs/'));
        if (false === file_exists($testAPIsPath)) {
            FileUtil::createDirectoryIfNotExist($testAPIsPath);
            $this->info('APIs Tests directory created');
        }

        $testRepositoriesPath = config('infyom.laravel_generator.path.repository_test', base_path('tests/Repositories/'));
        if (false === file_exists($testRepositoriesPath)) {
            FileUtil::createDirectoryIfNotExist($testRepositoriesPath);
            $this->info('Repositories Tests directory created');
        }
    }

    /**
     * Publish Base Controller.
     *
     * @return void
     */
    private function publishBaseController()
    {
        $templateData = get_artomator_template('app_base_controller');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = app_path('Http/Controllers/');

        $fileName = 'AppBaseController.php';

        if (true === file_exists($controllerPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('AppBaseController created');
    }

    /**
     * Publish Base Repository.
     *
     * @return void
     */
    private function publishBaseRepository()
    {
        $templateData = get_artomator_template('base_repository');

        $templateData = $this->fillTemplate($templateData);

        $repositoryPath = app_path('Repositories/');

        FileUtil::createDirectoryIfNotExist($repositoryPath);

        $fileName = 'BaseRepository.php';

        if (true === file_exists($repositoryPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($repositoryPath, $fileName, $templateData);

        $this->info('BaseRepository created');
    }

    /**
     * Publish Locale Files.
     *
     * @return void
     */
    private function publishLocaleFiles()
    {
        $localesDir = __DIR__.'/../../../locale/';

        $this->publishDirectory($localesDir, resource_path('lang'), 'lang', true);

        $this->comment('Locale files published');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['localized', null, InputOption::VALUE_NONE, 'Localize files.'],
        ];
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
