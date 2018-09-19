<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\WorkflowBundle\tests\integration\Storage\Sql\ProductModelDraft;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UpdateDraftAuthorIntegration extends TestCase
{

    public function testQueryToGetAssociatedProductCodes(): void
    {
        $this->createFamilyVariant();
        $productModel = $this->createProductModel('foo');

        $draft = $this->get('pimee_workflow.factory.product_model_draft')->createEntityWithValueDraft($productModel, 'admin');
        $draft->setValues(new ValueCollection());
        $this->get('pimee_workflow.saver.product_model_draft')->save($draft);

        $this->get('pimee_workflow.sql.product_model.update_draft_author')->execute('admin', 'new_admin');

        $connection = $this->get('database_connection');
        $result = $connection
            ->executeQuery('SELECT id FROM pimee_workflow_product_model_draft where author = "new_admin"')
            ->fetch(\PDO::FETCH_ASSOC);
        Assert::assertNotEmpty($result);
    }

    private function createFamilyVariant(): void
    {
        $this->createAttribute('boolean_axis');
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => 'family_test',
            'attributes'  => ['sku', 'boolean_axis'],
            'attribute_requirements' => ['ecommerce' => ['sku']
            ]
        ]);

        $errors = $this->get('validator')->validate($family);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.family')->save($family);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code' => 'family_variant_test',
            'family' => 'family_test',
            'variant_attribute_sets' => [
                [
                    'axes' => ['boolean_axis'],
                    'attributes' => [],
                    'level'=> 1
                ]
            ],
        ]);

        $errors = $this->get('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    /**
     * @param string $code
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    private function createProductModel(string $code) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => $code,
            'family_variant' => 'family_variant_test'
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_model')->refreshIndex();

        return $productModel;
    }

    /**
     * @param string $code
     */
    private function createAttribute(string $code): void
    {
        $data = [
            'code' => $code,
            'type' => AttributeTypes::BOOLEAN,
            'localizable' => false,
            'scopable' => false,
            'group' => 'other'
        ];

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
