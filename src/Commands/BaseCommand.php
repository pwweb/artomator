<?php

namespace PWWEB\Artomator\Commands;

use InfyOm\Generator\Commands\BaseCommand as Base;
use PWWEB\Artomator\Generators\GraphQL\GraphQLMutationGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;

class BaseCommand extends Base
{
    public function generateGraphQLItems()
    {
        if (! $this->isSkip('mutations') and ! $this->isSkip('graphql_mutations')) {
            $mutationGenerator = new GraphQLMutationGenerator($this->commandData);
            $mutationGenerator->generate();
        }

        if (! $this->isSkip('queries') and ! $this->isSkip('graphql_query')) {
            $queryGenerator = new GraphQLQueryGenerator($this->commandData);
            $queryGenerator->generate();
        }

        if (! $this->isSkip('types') and ! $this->isSkip('graphql_types')) {
            $typeGenerator = new GraphQLTypeGenerator($this->commandData);
            $typeGenerator->generate();
        }
    }
}
