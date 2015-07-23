<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

/**
 * Category access update guesser
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CategoryAccessUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return in_array($action, array(UpdateGuesserInterface::ACTION_UPDATE_ENTITY));
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof CategoryAccessInterface) {
            $pendings[] = $entity->getCategory();
        }

        return $pendings;
    }
}
