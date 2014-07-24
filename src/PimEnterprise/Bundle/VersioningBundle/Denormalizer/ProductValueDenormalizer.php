<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * TODO : Should be removed
 */
class ProductValueDenormalizer extends AbstractEntityDenormalizer
{
    /** @var \Pim\Bundle\CatalogBundle\Builder\ProductBuilder */
    protected $productBuilder;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     * @param ProductBuilder  $productBuilder
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityClass, ProductBuilder $productBuilder = null)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc]
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        echo get_class($this)."::denormalize<br />";
        $value = $context['entity'];
        echo "Attribute type: ". $value->getAttribute()->getAttributeType() ."<br />";


        // Call denormalizer with attribute type
        $this->serializer->denormalize($data, $value->getAttribute()->getAttributeType(), 'csv', $context);


        return $value;
    }
}
