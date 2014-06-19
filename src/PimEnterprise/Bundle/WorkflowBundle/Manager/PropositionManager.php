<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PropositionFactory;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\PropositionChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

/**
 * Manage product propositions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var PropositionFactory */
    protected $factory;

    /** @var PropositionRepositoryInterface */
    protected $repository;

    /** @var PropositionChangesApplier */
    protected $applier;

    /**
     * @param ManagerRegistry                $registry
     * @param ProductManager                 $manager
     * @param UserContext                    $userContext
     * @param PropositionFactory             $factory
     * @param PropositionRepositoryInterface $repository
     * @param PropositionChangesApplier      $applier
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductManager $manager,
        UserContext $userContext,
        PropositionFactory $factory,
        PropositionRepositoryInterface $repository,
        PropositionChangesApplier $applier
    ) {
        $this->registry = $registry;
        $this->manager = $manager;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
    }

    /**
     * Approve a proposition
     *
     * @param Proposition $proposition
     */
    public function approve(Proposition $proposition)
    {
        $product = $proposition->getProduct();

        $this->applier->apply($product, $proposition);

        $this->manager->handleMedia($product);
        $this->manager->saveProduct($product, ['bypass_proposition' => true]);
        $this->remove($proposition);
    }

    /**
     * Refuse a proposition
     *
     * @param Proposition $proposition
     */
    public function refuse(Proposition $proposition)
    {
        $this->remove($proposition);
    }

    /**
     * Remove a persisted proposition
     *
     * @param Proposition $proposition
     *
     */
    public function remove(Proposition $proposition)
    {
        $propositionManager = $this->registry->getManagerForClass(get_class($proposition));
        $propositionManager->remove($proposition);
        $propositionManager->flush();
    }

    /**
     * Find or create a proposition
     *
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return Proposition
     *
     * @throw \LogicException
     */
    // TODO (2014-06-18 17:05 by Gildas): Use this method in the PropositionPersister
    public function findOrCreate(ProductInterface $product, $locale)
    {
        if (null === $user = $this->userContext->getUser()) {
            throw new \LogicException('Current user cannot be resolved');
        }
        $username = $this->userContext->getUser()->getUsername();
        $proposition = $this->repository->findUserProposition($product, $username, $locale);

        if (null === $proposition) {
            $proposition = $this->factory->createProposition($product, $username, $locale);
        }

        return $proposition;
    }
}
