<?php

declare(strict_types=1);

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Removes invalid Sylius services that reference non-existent classes.
 * This is a workaround for a bug in Sylius 1.2.x where service definitions
 * were left behind after the classes were removed.
 *
 * @see https://github.com/Sylius/Sylius/issues/9123
 */
class RemoveInvalidSyliusServicesPass implements CompilerPassInterface
{
    private const INVALID_SERVICES = [
        'sylius.form.event_subscriber.build_promotion_action',
        'sylius.form.event_subscriber.build_promotion_rule',
        'sylius.form.type.data_transformer.product_variants_to_codes',
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::INVALID_SERVICES as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $container->removeDefinition($serviceId);
            }
        }
    }
}