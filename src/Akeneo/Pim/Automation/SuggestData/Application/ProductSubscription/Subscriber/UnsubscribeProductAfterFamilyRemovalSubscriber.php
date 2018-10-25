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

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\SuggestData\Domain\Query\Product\SelectProductFamilyIdQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class UnsubscribeProductAfterFamilyRemovalSubscriber implements EventSubscriberInterface
{
    /** @var SelectProductFamilyIdQueryInterface */
    private $selectProductFamilyIdQuery;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /**
     * @param SelectProductFamilyIdQueryInterface $selectProductFamilyIdQuery
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(
        SelectProductFamilyIdQueryInterface $selectProductFamilyIdQuery,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ) {
        $this->selectProductFamilyIdQuery = $selectProductFamilyIdQuery;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'unsubscribeProduct',
        ];
    }

    /**
     * Unsubscribe product when family.
     *
     * @param GenericEvent $event
     */
    public function unsubscribeProduct(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (null === $product->getId() || null !== $product->getFamily()) {
            return;
        }

        if (null !== $this->selectProductFamilyIdQuery->execute($product->getId())) {
            try {
                $command = new UnsubscribeProductCommand($product->getId());
                $this->unsubscribeProductHandler->handle($command);
            } catch (ProductNotSubscribedException $e) {
                // Silently catch exception if the product is not subscribed
                return;
            }
        }
    }
}
