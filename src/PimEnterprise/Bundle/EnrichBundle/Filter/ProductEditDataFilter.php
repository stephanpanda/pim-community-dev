<?php

namespace PimEnterprise\Bundle\EnrichBundle\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Filter\ProductEditDataFilter as BaseProductEditFilter;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Enterprise override to add product ownership check for product classification
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductEditDataFilter extends BaseProductEditFilter
{
    /**
     * {@inheritdoc}
     */
    protected function isAllowedToClassify(ProductInterface $product)
    {
        return $this->securityFacade->isGranted(Attributes::OWN, $product);
    }
}
