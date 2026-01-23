<?php

declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Fixture\Factory\BouquetProductExampleFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class BouquetProductFixture extends AbstractFixture
{
    private ObjectManager $objectManager;
    private BouquetProductExampleFactory $exampleFactory;

    public function __construct(ObjectManager $objectManager, BouquetProductExampleFactory $exampleFactory)
    {
        $this->objectManager = $objectManager;
        $this->exampleFactory = $exampleFactory;
    }

    public function getName(): string
    {
        return 'bouquet_product';
    }

    public function load(array $options): void
    {
        foreach ($options['custom'] as $productData) {
            $product = $this->exampleFactory->create($productData);
            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->arrayNode('custom')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('code')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('description')->isRequired()->end()
                            ->floatNode('price')->isRequired()->end()
                            ->scalarNode('taxon')->defaultNull()->end()
                            ->scalarNode('occasion')->defaultNull()->end()
                            ->scalarNode('availability')->defaultValue('en_stock')->end()
                            ->arrayNode('images')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('path')->isRequired()->end()
                                        ->scalarNode('type')->defaultValue('main')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
