<?php

declare(strict_types=1);

namespace AppBundle\Fixture\Factory;

use AppBundle\Entity\Product;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BouquetProductExampleFactory extends AbstractExampleFactory
{
    private FactoryInterface $productFactory;
    private FactoryInterface $productVariantFactory;
    private FactoryInterface $productImageFactory;
    private RepositoryInterface $taxonRepository;
    private ChannelRepositoryInterface $channelRepository;
    private SlugGeneratorInterface $slugGenerator;
    private ImageUploaderInterface $imageUploader;
    private OptionsResolver $optionsResolver;

    public function __construct(
        FactoryInterface $productFactory,
        FactoryInterface $productVariantFactory,
        FactoryInterface $productImageFactory,
        RepositoryInterface $taxonRepository,
        ChannelRepositoryInterface $channelRepository,
        SlugGeneratorInterface $slugGenerator,
        ImageUploaderInterface $imageUploader
    ) {
        $this->productFactory = $productFactory;
        $this->productVariantFactory = $productVariantFactory;
        $this->productImageFactory = $productImageFactory;
        $this->taxonRepository = $taxonRepository;
        $this->channelRepository = $channelRepository;
        $this->slugGenerator = $slugGenerator;
        $this->imageUploader = $imageUploader;

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
    }

    public function create(array $options = []): ProductInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var Product $product */
        $product = $this->productFactory->createNew();

        $product->setCode($options['code']);
        $product->setEnabled(true);

        // Set translations
        $product->setCurrentLocale('fr_FR');
        $product->setFallbackLocale('fr_FR');
        $product->setName($options['name']);
        $product->setSlug($this->slugGenerator->generate($options['name']));
        $product->setDescription($options['description']);

        // Set custom fields
        $product->setOccasion($options['occasion']);
        $product->setAvailability($options['availability']);

        // Add to channels
        /** @var ChannelInterface $channel */
        foreach ($options['channels'] as $channel) {
            $product->addChannel($channel);
        }

        // Set main taxon
        if ($options['taxon'] !== null) {
            /** @var TaxonInterface|null $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $options['taxon']]);
            if ($taxon !== null) {
                $product->setMainTaxon($taxon);
                $product->addProductTaxon($this->createProductTaxon($product, $taxon));
            }
        }

        // Set occasion taxon
        if ($options['occasion'] !== null) {
            /** @var TaxonInterface|null $occasionTaxon */
            $occasionTaxon = $this->taxonRepository->findOneBy(['code' => $options['occasion']]);
            if ($occasionTaxon !== null) {
                $product->addProductTaxon($this->createProductTaxon($product, $occasionTaxon));
            }
        }

        // Create variant with price
        $this->createVariant($product, $options);

        // Add images
        foreach ($options['images'] as $imageData) {
            $this->createImage($product, $imageData);
        }

        return $product;
    }

    private function createVariant(ProductInterface $product, array $options): void
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->productVariantFactory->createNew();

        $variant->setCode($options['code'] . '-variant');
        $variant->setName($options['name']);
        $variant->setProduct($product);

        // Set price for each channel
        /** @var ChannelInterface $channel */
        foreach ($options['channels'] as $channel) {
            $variant->addChannelPricing($this->createChannelPricing($variant, $channel, $options['price']));
        }

        $product->addVariant($variant);
    }

    private function createChannelPricing(ProductVariantInterface $variant, ChannelInterface $channel, float $price)
    {
        $channelPricing = new \Sylius\Component\Core\Model\ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice((int) ($price * 100)); // Convert to cents
        $channelPricing->setOriginalPrice((int) ($price * 100));

        return $channelPricing;
    }

    private function createProductTaxon(ProductInterface $product, TaxonInterface $taxon)
    {
        $productTaxon = new \Sylius\Component\Core\Model\ProductTaxon();
        $productTaxon->setProduct($product);
        $productTaxon->setTaxon($taxon);

        return $productTaxon;
    }

    private function createImage(ProductInterface $product, array $imageData): void
    {
        $imagePath = $imageData['path'];

        if (!file_exists($imagePath)) {
            return;
        }

        $productImage = $this->productImageFactory->createNew();
        $productImage->setType($imageData['type'] ?? 'main');

        $uploadedFile = new UploadedFile($imagePath, basename($imagePath));
        $productImage->setFile($uploadedFile);

        $this->imageUploader->upload($productImage);

        $product->addImage($productImage);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['code', 'name', 'description', 'price'])
            ->setDefault('taxon', null)
            ->setDefault('occasion', null)
            ->setDefault('availability', 'en_stock')
            ->setDefault('images', [])
            ->setDefault('channels', LazyOption::all($this->channelRepository))
            ->setAllowedTypes('code', 'string')
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('description', 'string')
            ->setAllowedTypes('price', ['int', 'float'])
            ->setAllowedTypes('taxon', ['null', 'string'])
            ->setAllowedTypes('occasion', ['null', 'string'])
            ->setAllowedTypes('availability', 'string')
            ->setAllowedTypes('images', 'array')
            ->setNormalizer('channels', LazyOption::findBy($this->channelRepository, 'code'))
        ;
    }
}
