<?php

namespace PWWEB\Artomator\Commands\Publish;

use InfyOm\Generator\Commands\Publish\PublishBaseCommand;
use InfyOm\Generator\Utils\FileUtil;

class PublishUserCommand extends PublishBaseCommand
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
    protected $name = 'artomator.publish:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes Users CRUD file';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->copyViews();
        $this->updateRoutes();
        $this->updateMenu();
        $this->publishUserController();
        if (true === config('infyom.laravel_generator.options.repository_pattern')) {
            $this->publishUserRepository();
        }
        $this->publishCreateUserRequest();
        $this->publishUpdateUserRequest();
    }

    /**
     * Copy Views.
     *
     * @return void
     */
    private function copyViews()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $this->createDirectories($viewsPath.'users');

        $files = $this->getViews();

        foreach ($files as $stub => $blade) {
            $sourceFile = get_template_file_path('scaffold/'.$stub, $templateType);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    /**
     * Create Directories.
     *
     * @param string $dir Directory.
     *
     * @return void
     */
    private function createDirectories($dir)
    {
        FileUtil::createDirectoryIfNotExist($dir);
    }

    /**
     * Get Views.
     *
     * @return array
     */
    private function getViews()
    {
        return [
            'users/create'      => 'users/create.blade.php',
            'users/edit'        => 'users/edit.blade.php',
            'users/fields'      => 'users/fields.blade.php',
            'users/index'       => 'users/index.blade.php',
            'users/show'        => 'users/show.blade.php',
            'users/show_fields' => 'users/show_fields.blade.php',
            'users/table'       => 'users/table.blade.php',
        ];
    }

    /**
     * Update Routes.
     *
     * @return void
     */
    private function updateRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', base_path('routes/web.php'));

        $routeContents = file_get_contents($path);

        $routesTemplate = get_artomator_template('routes.user');

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($path, $routeContents);
        $this->comment("\nUser route added");
    }

    /**
     * Update Menu.
     *
     * @return void
     */
    private function updateMenu()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
        $path = $viewsPath.'layouts/menu.blade.php';
        $menuContents = file_get_contents($path);
        $sourceFile = file_get_contents(get_template_file_path('scaffold/users/menu', $templateType));
        $menuContents .= "\n".$sourceFile;

        file_put_contents($path, $menuContents);
        $this->comment("\nUser Menu added");
    }

    /**
     * Publish User Controller.
     *
     * @return void
     */
    private function publishUserController()
    {
        $templateData = get_template('user/user_controller', 'laravel-generator');
        if (false === config('infyom.laravel_generator.options.repository_pattern')) {
            $templateData = get_template('user/user_controller_without_repository', 'laravel-generator');
            $templateData = $this->fillTemplate($templateData);
        }

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = config('infyom.laravel_generator.path.controller', app_path('Http/Controllers/'));

        $fileName = 'UserController.php';

        if (true === file_exists($controllerPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('UserController created');
    }

    /**
     * Publish User Repository.
     *
     * @return void
     */
    private function publishUserRepository()
    {
        $templateData = get_template('user/user_repository', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $repositoryPath = config('infyom.laravel_generator.path.repository', app_path('Repositories/'));

        $fileName = 'UserRepository.php';

        FileUtil::createDirectoryIfNotExist($repositoryPath);

        if (true === file_exists($repositoryPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($repositoryPath, $fileName, $templateData);

        $this->info('UserRepository created');
    }

    /**
     * Publish Create User Request.
     *
     * @return void
     */
    private function publishCreateUserRequest()
    {
        $templateData = get_template('user/create_user_request', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $requestPath = config('infyom.laravel_generator.path.request', app_path('Http/Requests/'));

        $fileName = 'CreateUserRequest.php';

        FileUtil::createDirectoryIfNotExist($requestPath);

        if (true === file_exists($requestPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($requestPath, $fileName, $templateData);

        $this->info('CreateUserRequest created');
    }

    /**
     * Publish Update User Request.
     *
     * @return void
     */
    private function publishUpdateUserRequest()
    {
        $templateData = get_template('user/update_user_request', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $requestPath = config('infyom.laravel_generator.path.request', app_path('Http/Requests/'));

        $fileName = 'UpdateUserRequest.php';
        if (true === file_exists($requestPath.$fileName) && false === $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($requestPath, $fileName, $templateData);

        $this->info('UpdateUserRequest created');
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
    private function fillTemplate($templateData)
    {
        $templateData = str_replace('$NAMESPACE_CONTROLLER$', config('infyom.laravel_generator.namespace.controller'), $templateData);

        $templateData = str_replace('$NAMESPACE_REQUEST$', config('infyom.laravel_generator.namespace.request'), $templateData);

        $templateData = str_replace('$NAMESPACE_REPOSITORY$', config('infyom.laravel_generator.namespace.repository'), $templateData);
        $templateData = str_replace('$NAMESPACE_USER$', config('auth.providers.users.model'), $templateData);

        // return $templateData;
        // ADDED THE FOLLOWING LINE:
        return $this->fillLicense($templateData);
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
