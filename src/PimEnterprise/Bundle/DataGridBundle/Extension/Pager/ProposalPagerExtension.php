<?php

namespace PimEnterprise\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Pager\AbstractPagerExtension;
use PimEnterprise\Bundle\DataGridBundle\Datasource\ProposalDatasource;

/**
 * Proposal pager extension
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalPagerExtension extends AbstractPagerExtension
{
    /**
     * {@inheritdoc}
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return $datasourceType === ProposalDatasource::TYPE;
    }
}
