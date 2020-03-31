<?php

namespace PWWEB\Artomator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputOption;

class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * ALL REFERENCES TO (get_template\(\')([a-z_\.]+)(', 'laravel-generator'\))
     * REPLACED WITH: get_artomator_template('$2')
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
        if ($repositoryPattern) {
            $this->publishBaseRepository();
        }
        if ($this->option('localized')) {
            $this->publishLocaleFiles();
        }
    }

    /**
     * Replaces dynamic variables of template.
     * THIS IS A NEW FUNCTION ADDED.
     *
     * @param string $templateData
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
     * @param string $templateData
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
        $appNamespace = substr($appNamespace, 0, strlen($appNamespace) - 1);
        $templateData = str_replace('$NAMESPACE_APP$', $appNamespace, $templateData);

        // return $templateData;
        // ADDED THE FOLLOWING LINE:
        return $this->fillLicense($templateData);
    }

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

        if (file_exists($testsPath.$fileName) && ! $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($testsPath, $fileName, $templateData);
        $this->info('ApiTestTrait created');

        $testAPIsPath = config('infyom.laravel_generator.path.api_test', base_path('tests/APIs/'));
        if (! file_exists($testAPIsPath)) {
            FileUtil::createDirectoryIfNotExist($testAPIsPath);
            $this->info('APIs Tests directory created');
        }

        $testRepositoriesPath = config('infyom.laravel_generator.path.repository_test', base_path('tests/Repositories/'));
        if (! file_exists($testRepositoriesPath)) {
            FileUtil::createDirectoryIfNotExist($testRepositoriesPath);
            $this->info('Repositories Tests directory created');
        }
    }

    private function publishBaseController()
    {
        $this->info('Boo!');
        $templateData = get_artomator_template('app_base_controller');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = app_path('Http/Controllers/');

        $fileName = 'AppBaseController.php';

        if (file_exists($controllerPath.$fileName) && ! $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('AppBaseController created');
    }

    private function publishBaseRepository()
    {
        $templateData = get_artomator_template('base_repository');

        $templateData = $this->fillTemplate($templateData);

        $repositoryPath = app_path('Repositories/');

        FileUtil::createDirectoryIfNotExist($repositoryPath);

        $fileName = 'BaseRepository.php';

        if (file_exists($repositoryPath.$fileName) && ! $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($repositoryPath, $fileName, $templateData);

        $this->info('BaseRepository created');
    }

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
