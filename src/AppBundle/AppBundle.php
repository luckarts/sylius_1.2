<?php

declare(strict_types=1);

namespace AppBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use AppBundle\DependencyInjection\Compiler\RemoveInvalidSyliusServicesPass;

class AppBundle extends Bundle
{

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RemoveInvalidSyliusServicesPass());
    }
}
