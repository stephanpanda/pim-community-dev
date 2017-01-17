<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;

/**
 * Setter action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SetterActionApplier implements ActionApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /**
     * @param PropertySetterInterface $propertySetter
     */
    public function __construct(PropertySetterInterface $propertySetter)
    {
        $this->propertySetter = $propertySetter;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $products = [])
    {
        foreach ($products as $product) {
            $this->propertySetter->setData(
                $product,
                $action->getField(),
                $action->getValue(),
                $action->getOptions()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductSetActionInterface;
    }
}