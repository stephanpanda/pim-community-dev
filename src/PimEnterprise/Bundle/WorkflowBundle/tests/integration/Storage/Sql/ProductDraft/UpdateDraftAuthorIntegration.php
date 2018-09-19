<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\WorkflowBundle\tests\integration\Storage\Sql\ProductDraft;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\ValueCollection;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UpdateDraftAuthorIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes(): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.saver.product')->save($product);

        $draft = $this->get('pimee_workflow.factory.product_draft')->createEntityWithValueDraft($product, 'admin');
        $draft->setValues(new ValueCollection());
        $this->get('pimee_workflow.saver.product_draft')->save($draft);

        $this->get('pimee_workflow.sql.product.update_draft_author')->execute('admin', 'new_admin');

        $connection = $this->get('database_connection');
        $result = $connection
            ->executeQuery('SELECT id FROM pimee_workflow_product_draft where author = "new_admin"')
            ->fetch(\PDO::FETCH_ASSOC);
        Assert::assertNotEmpty($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
