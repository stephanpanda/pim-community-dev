<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use PimEnterprise\Bundle\SecurityBundle\Datagrid\EventListener\ConfigureProductGridListener;
use PimEnterprise\Bundle\SecurityBundle\Datagrid\Product\ContextConfigurator;
use PimEnterprise\Bundle\SecurityBundle\Datagrid\Product\RowActionsConfigurator;

class ConfigureProductGridListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigureProductGridListener::class);
    }

    function let(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        RowActionsConfigurator $rowActionsConfigurator
    ) {
        $this->beConstructedWith(
            $contextConfigurator,
            $columnsConfigurator,
            $filtersConfigurator,
            $sortersConfigurator,
            $rowActionsConfigurator
        );
    }

    function it_builds_the_datagrid(BuildBefore $event, DatagridConfiguration $dataGridConfiguration)
    {
        $event->getConfig()->willReturn($dataGridConfiguration);

        $this->buildBefore($event);
    }
}