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

namespace Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Handles a SubscribeProduct command.
 *
 * It checks that the product exists and creates the product subscription
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeProductHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->productRepository = $productRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param SubscribeProduct $command
     */
    public function handle(SubscribeProduct $command): void
    {
        $identifiersMapping = $this->identifiersMappingRepository->findAll();
        if ($identifiersMapping->isEmpty()) {
            throw new \Exception('Identifiers mapping has not identifier defined');
        }

        $product = $this->productRepository->find($command->getProductId());
        if (null === $product) {
            throw new \Exception(sprintf('Could not find product with id "%s"', $command->getProductId()));
        }

        $this->subscribe($product);
    }

    /**
     * Creates a subscription request, sends it to the data provider and saves the resulting subscription
     *
     * @param ProductInterface $product
     */
    private function subscribe(ProductInterface $product): void
    {
        // nothing yet
    }
}
