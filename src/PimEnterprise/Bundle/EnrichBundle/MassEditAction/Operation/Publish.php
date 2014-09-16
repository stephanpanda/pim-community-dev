<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Batch operation to publish products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Publish extends ProductMassEditOperation
{
    /**
     * @var PublishedProductManager
     */
    protected $manager;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param PublishedProductManager  $manager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(PublishedProductManager $manager, SecurityContextInterface $securityContext)
    {
        $this->manager         = $manager;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_publish';
    }

    /**
     * The list of not granted product identifiers
     *
     * @return string
     */
    public function getNotGrantedIdentifiers()
    {
        $products   = $this->getObjectsToMassEdit();
        $notGranted = [];
        foreach ($products as $product) {
            if ($this->securityContext->isGranted(Attributes::OWN, $product) === false) {
                $notGranted[] = (string) $product->getIdentifier();
            }
        }

        return implode(', ', $notGranted);
    }

    /**
     * Allows to set the form but we don't use not granted data from it
     *
     * @param string $notGranted
     *
     * @return Publish
     */
    public function setNotGrantedIdentifiers($notGranted)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        if (!$this->securityContext->isGranted(Attributes::OWN, $product)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot publish product "%s" because current user does not own it',
                    (string) $product
                )
            );
        }

        $this->manager->publish($product);
    }
}
