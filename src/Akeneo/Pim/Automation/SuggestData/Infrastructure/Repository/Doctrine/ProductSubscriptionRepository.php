<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $className;

    /**
     * @param EntityManagerInterface $em
     * @param string $className
     */
    public function __construct(EntityManagerInterface $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscription $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductAndSubscriptionId(
        ProductInterface $product,
        string $subscriptionId
    ): ?ProductSubscription {
        $repository = $this->em->getRepository($this->className);

        return $repository->findOneBy(
            [
                'product'        => $product,
                'subscriptionId' => $subscriptionId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        return $this->em->getRepository($this->className)->findOneByProduct($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingSubscriptions(): array
    {
        $qb = $this->em->createQueryBuilder()->select('subscription')->from($this->className, 'subscription');
        $qb->where(
            $qb->expr()->isNotNull('subscription.rawSuggestedData')
        );

        return $qb->getQuery()->getResult();
    }
}
