<?php

namespace PWWEB\Artomator\Generators\GraphQL;

use InfyOm\Generator\Generators\BaseGenerator;
use PWWEB\Artomator\Common\CommandData;
use PWWEB\Artomator\Utils\FileUtil;

class GraphQLMutationGenerator extends BaseGenerator
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
        $this->path = $commandData->config->pathGraphQLMutation;
        $this->createFileName = 'create'.$this->commandData->modelName.'Mutation.php';
        $this->updateFileName = 'update'.$this->commandData->modelName.'Mutation.php';
        $this->deleteFileName = 'delete'.$this->commandData->modelName.'Mutation.php';
    }

    public function generate()
    {
        $this->generateCreateMutation();
        $this->generateUpdateMutation();
        $this->generateDeleteMutation();
    }

    private function generateCreateMutation()
    {
        $templateData = get_template('graphql.mutation.create_mutation', 'artomator');

        $templateData = str_replace('$ARGUMENTS$', $this->generateArguments(), $templateData);
        $templateData = str_replace('$RESOLVES$', $this->generateResolves(), $templateData);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->createFileName, $templateData);

        $this->commandData->commandComment("\nCreate Mutation created: ");
        $this->commandData->commandInfo($this->createFileName);
    }

    private function generateUpdateMutation()
    {
        $templateData = get_template('graphql.mutation.update_mutation', 'artomator');

        $templateData = str_replace('$ARGUMENTS$', $this->generateArguments(), $templateData);
        $templateData = str_replace('$RESOLVES$', $this->generateResolves(), $templateData);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->commandData->commandComment("\nUpdate Mutation created: ");
        $this->commandData->commandInfo($this->updateFileName);
    }

    private function generateDeleteMutation()
    {
        $templateData = get_template('graphql.mutation.delete_mutation', 'artomator');

        $templateData = str_replace('$ARGUMENTS$', $this->generateArguments(), $templateData);
        $templateData = str_replace('$RESOLVES$', $this->generateResolves(), $templateData);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->deleteFileName, $templateData);

        $this->commandData->commandComment("\nDelete Mutation created: ");
        $this->commandData->commandInfo($this->deleteFileName);
    }

    private function generateArguments()
    {
        $arguments = [];
        foreach ($this->commandData->fields as $field) {
            if (true === in_array($field->name, ['created_at', 'updated_at', 'id'])) {
                continue;
            }
            if (true === $field->isNotNull) {
                $field_type = 'Type::nonNull(Type::'.$field->fieldType.'())';
            } else {
                $field_type = 'Type::'.$field->fieldType.'()';
            }

            $arguments[] = "'".$field->name."' => [".arty_nl_tab(1, 4)."'name' => '".$field->name."',".arty_nl_tab(1, 4)."'type' => ".$field_type.','.arty_nl_tab(1, 3).'],';
        }

        return implode(arty_nl_tab(1, 3), $arguments);
    }

    private function generateResolves()
    {
        $resolves = [];
        foreach ($this->commandData->fields as $field) {
            if (true === in_array($field->name, ['created_at', 'updated_at', 'id'])) {
                continue;
            }

            $resolves[] = "'".$field->name."' => \$args['".$field->name."'],";
        }

        return implode(arty_nl_tab(1, 3), $resolves);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->commandData->commandComment('Create GraphQL Mutation file deleted: '.$this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->commandData->commandComment('Update GraphQL Mutation file deleted: '.$this->updateFileName);
        }

        if ($this->rollbackFile($this->path, $this->deleteFileName)) {
            $this->commandData->commandComment('Delete GraphQL Mutation file deleted: '.$this->deleteFileName);
        }
    }
}
