<?php

namespace spec\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Selector\Orm\Attribute;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;

class IsSmartSelectorExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Attribute', 'Resource');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            'Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Selector\Orm\Attribute\IsSmartSelectorExtension'
        );
    }

    function it_is_a_datagrid_extension()
    {
        $this->shouldImplement('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
    }

    function it_applies_only_to_the_attribute_grid(DatagridConfiguration $config)
    {
        $config->getName()->willReturn('foo');
        $this->isApplicable($config)->shouldReturn(false);

        $config->getName()->willReturn('attribute-grid');
        $this->isApplicable($config)->shouldReturn(true);
    }

    function it_joins_and_selects_the_smart_property_of_attributes(
        DatasourceInterface $ds,
        DatagridConfiguration $config,
        QueryBuilder $qb,
        Expr $expr
    ) {
        $ds->getQueryBuilder()->willReturn($qb);
        $qb->getRootAliases()->willReturn(['a']);
        $qb->expr()->willReturn($expr);

        $expr->andX(null, null)->shouldBeCalled();
        $expr->eq("r.resourceId", "a.id")->shouldBeCalled();
        $expr->eq("r.resourceName", null)->shouldBeCalled();
        $expr->literal("Attribute")->shouldBeCalled();

        $qb
            ->leftJoin(
                'Resource',
                'r',
                'WITH',
                null
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->addSelect('CASE WHEN r.resourceId IS NULL THEN false ELSE true END AS is_smart')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->distinct(true)->shouldBeCalled();

        $this->visitDatasource($config, $ds);
    }
}