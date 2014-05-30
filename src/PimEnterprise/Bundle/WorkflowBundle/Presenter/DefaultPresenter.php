<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Presenter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (isset($change['id'])) {
            unset($change['id']);
        }

        return array_pop($change);
    }
}
