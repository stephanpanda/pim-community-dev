<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Persistence\ProductChangesProvider;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PimEnterprise\Bundle\CatalogBundle\Persistence\Engine\ArrayDiff;

/**
 * Provide changes that happened on a product values
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EntityChangesProvider implements ProductChangesProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ManagerRegistry     $registry
     * @param NormalizerInterface $normalizer
     * @param ArrayDiff           $engine
     */
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer,
        ArrayDiff $engine = null
    ) {
        $this->registry = $registry;
        $this->normalizer = $normalizer;
        $this->engine = $engine ?: new ArrayDiff();
    }

    /**
     * {@inheritdoc}
     */
    public function computeNewValues(ProductInterface $product)
    {
        $manager = $this->registry->getManagerForClass(get_class($product));

        $current = [];
        foreach ($product->getValues() as $value) {
            $current[$value->getId()] = $this->normalizer->normalize($value, 'json', ['locales' => ['en_US']]);
            $manager->refresh($value);
        }

        $previous = [];
        foreach ($product->getValues() as $value) {
            $previous[$value->getId()] = $this->normalizer->normalize($value, 'json', ['locales' => ['en_US']]);
        }

        return $this->engine->diff($previous, $current);
    }
}
