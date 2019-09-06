<?php

namespace PWWEB\Artomator\Generators\API;

use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Generators\BaseGenerator;
use PWWEB\Artomator\Utils\FileUtil;

class APIMutationGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $createFileName;

    /**
     * @var string
     */
    private $updateFileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathApiMutation;
        $this->createFileName = 'Create'.$this->commandData->modelName.'Mutation.php';
        $this->updateFileName = 'Update'.$this->commandData->modelName.'Mutation.php';
        $this->deleteFileName = 'Delete'.$this->commandData->modelName.'Mutation.php';
    }

    public function generate()
    {
        $this->generateCreateMutation();
        $this->generateUpdateMutation();
        $this->generateDeleteMutation();
    }

    private function generateCreateMutation()
    {
        $templateData = get_template('api.mutation.create_mutation', 'artomator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->createFileName, $templateData);

        $this->commandData->commandComment("\nCreate Mutation created: ");
        $this->commandData->commandInfo($this->createFileName);
    }

    private function generateUpdateMutation()
    {
        $templateData = get_template('api.mutation.update_mutation', 'artomator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->commandData->commandComment("\nUpdate Mutation created: ");
        $this->commandData->commandInfo($this->updateFileName);
    }

    private function generateDeleteMutation()
    {
        $templateData = get_template('api.mutation.delete_mutation', 'artomator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->deleteFileName, $templateData);

        $this->commandData->commandComment("\nDelete Mutation created: ");
        $this->commandData->commandInfo($this->deleteFileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->commandData->commandComment('Create API Mutation file deleted: '.$this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->commandData->commandComment('Update API Mutation file deleted: '.$this->updateFileName);
        }

        if ($this->rollbackFile($this->path, $this->deleteFileName)) {
            $this->commandData->commandComment('Delete API Mutation file deleted: '.$this->deleteFileName);
        }
    }
}
