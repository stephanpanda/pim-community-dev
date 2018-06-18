<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Updater\ExternalApi;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class AssetUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    protected $assetUpdater;

    /**
     * @param ObjectUpdaterInterface $assetUpdater
     */
    public function __construct(ObjectUpdaterInterface $assetUpdater)
    {
        $this->assetUpdater = $assetUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($asset, array $data, array $options = [])
    {
        if (!$asset instanceof AssetInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($asset),
                AssetInterface::class
            );
        }

        if (null === $asset->getId() && !array_key_exists('localizable', $data)) {
            $data['localizable'] = false;
        }

        unset($data['variation_files']);
        unset($data['reference_files']);

        $this->assetUpdater->update($asset, $data, $options);
    }
}