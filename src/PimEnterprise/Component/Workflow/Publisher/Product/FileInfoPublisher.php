<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Publisher\Product;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;

/**
 * Product file publisher
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FileInfoPublisher implements PublisherInterface
{
    /**
     * {@inheritdoc}
     */
    public function publish($file, array $options = [])
    {
        // we don't have to do something special here,
        // we return the file because the media
        // link is copied in the product value via the ValuePublisher
        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof FileInfoInterface;
    }
}