<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present metric data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricPresenter extends AbstractPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('metric', $change);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return sprintf('%s %s', $data->getData(), $data->getUnit());
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return sprintf('%s %s', $change['metric']['data'], $change['metric']['unit']);
    }
}
