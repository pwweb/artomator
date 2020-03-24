<?php

namespace PWWEB\Artomator\Commands;

use InfyOm\Generator\Commands\BaseCommand as Base;
use PWWEB\Artomator\Generators\GraphQL\GraphQLMutationGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLQueryGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLSubscriptionGenerator;
use PWWEB\Artomator\Generators\GraphQL\GraphQLTypeGenerator;

class BaseCommand extends Base
{
    public function generateGraphQLItems()
    {
        if (($this->isSkip('mutations') or $this->isSkip('graphql_mutations')) === false) {
            $mutationGenerator = new GraphQLMutationGenerator($this->commandData);
            $mutationGenerator->generate();
        }

        if (($this->isSkip('queries') or $this->isSkip('graphql_query')) === false) {
            $queryGenerator = new GraphQLQueryGenerator($this->commandData);
            $queryGenerator->generate();
        }

        if (($this->isSkip('types') or $this->isSkip('graphql_types')) === false) {
            $typeGenerator = new GraphQLTypeGenerator($this->commandData);
            $typeGenerator->generate();
        }

        if ((($this->isSkip('subscription') or $this->isSkip('graphql_subscription')) === false) and config('pwweb.artomator.options.subscription')) {
            $subscriptionGenerator = new GraphQLSubscriptionGenerator($this->commandData);
            $subscriptionGenerator->generate();
        }
    }
}
