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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepositoryIntegration extends TestCase
{
    public function test_it_saves_a_product_subscription()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = 'a-random-string';
        $subscription = new ProductSubscription($product, $subscriptionId, ['foo' => 'bar']);
        $this->getRepository()->save($subscription);

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT product_id, subscription_id, suggested_data from pim_suggest_data_product_subscription;'
        );
        $retrievedSubscriptions = $statement->fetchAll();

        Assert::assertCount(1, $retrievedSubscriptions);
        Assert::assertEquals(
            [
                'product_id'      => $product->getId(),
                'subscription_id' => $subscriptionId,
                'suggested_data'  => '{"foo": "bar"}',
            ],
            $retrievedSubscriptions[0]
        );
    }

    public function test_it_finds_a_subscription_by_product_and_subscription_id()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();
        $suggestedData = [
            'an_attribute'      => 'some data',
            'another_attribute' => 'some other data',
        ];

        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare(
            'INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, suggested_data) VALUES (:productId, :subscriptionId, :suggestedData)'
        );
        $statement->execute(
            [
                'productId'      => $product->getId(),
                'subscriptionId' => $subscriptionId,
                'suggestedData'  => json_encode($suggestedData),
            ]
        );

        $subscription = $this->getRepository()->findOneByProductAndSubscriptionId($product, $subscriptionId);
        Assert::assertInstanceOf(ProductSubscriptionInterface::class, $subscription);
        Assert::assertSame($product, $subscription->getProduct());
        Assert::assertSame($subscriptionId, $subscription->getSubscriptionId());
        Assert::assertSame($suggestedData, $subscription->getSuggestedData());
    }

    function test_that_it_gets_a_subscription_status_for_a_subscribed_product_id()
    {
        $product = $this->createProduct('a_product');
        $subscriptionId = uniqid();

        $query = <<<SQL
INSERT INTO pim_suggest_data_product_subscription (product_id, subscription_id, suggested_data) 
VALUES (:productId, :subscriptionId, :suggestedData)
SQL;

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute([
            'productId'      => $product->getId(),
            'subscriptionId' => $subscriptionId,
            'suggestedData'  => json_encode([]),
        ]);

        $subscriptionStatus = $this->getRepository()->getSubscriptionStatusForProductId($product->getId());

        Assert::assertTrue(is_array($subscriptionStatus));
        Assert::assertSame(
            ['subscription_id' => $subscriptionId],
            $subscriptionStatus
        );
    }

    function test_that_it_gets_a_subscription_status_for_a_non_subscribed_product_id()
    {
        $subscriptionStatus = $this->getRepository()->getSubscriptionStatusForProductId(42);

        Assert::assertTrue(is_array($subscriptionStatus));
        Assert::assertSame(
            ['subscription_id' => ''],
            $subscriptionStatus
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    private function createProduct(string $identifier): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @return ProductSubscriptionRepositoryInterface
     */
    private function getRepository(): ProductSubscriptionRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.suggest_data.repository.product_subscription');
    }
}
