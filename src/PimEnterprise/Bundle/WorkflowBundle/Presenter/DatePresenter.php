<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present changes on date data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('date', $change);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data->format('F, d Y');
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return (new \DateTime($change['date']))->format('F, d Y');
    }
}
