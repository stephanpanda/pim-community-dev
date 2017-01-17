<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Filter;

use Akeneo\Component\Batch\Model\JobInstance;
use PimEnterprise\Bundle\CatalogBundle\Filter\AbstractAuthorizationFilter;
use PimEnterprise\Component\Security\Attributes;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;

/**
 * Job instance filter for execution
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class JobInstanceExecuteRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($jobInstance, $type, array $options = [])
    {
        if (!$this->supportsObject($jobInstance, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "JobInstance"');
        }

        return !$this->authorizationChecker->isGranted(
            Attributes::EXECUTE,
            $jobInstance
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof JobInstance;
    }
}