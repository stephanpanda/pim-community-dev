<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Bundle\Doctrine\ORM\Repository;

use Akeneo\Asset\Component\Repository\ChannelConfigurationRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Channel variations configuration repository
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ChannelConfigurationRepository extends EntityRepository implements ChannelConfigurationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['channel'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['channel' => $identifier]);
    }
}