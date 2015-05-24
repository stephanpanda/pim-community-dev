<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use League\Flysystem\FilesystemInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Download the raw file of a file stored in a virtual filesystem
 * into the temporary directory of the local filesystem.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class RawFileDownloader implements RawFileDownloaderInterface
{
    const TMP_DIRECTORY = 'download/';

    /**
     * {@inheritdoc}
     */
    public function download(FileInterface $file, FilesystemInterface $filesystem)
    {
        if (!$filesystem->has($file->getPathname())) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $file->getPathname()));
        }

        $tmpDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . RawFileDownloader::TMP_DIRECTORY;
        if (!is_dir($tmpDirectory)) {
            mkdir($tmpDirectory);
        }

        $localPathname = $tmpDirectory . uniqid();

        if (false === $stream = $filesystem->readStream($file->getPathname())) {
            throw new \LogicException(
                sprintf('Unable to download the file "%s" from the filesystem.', $file->getPathname())
            );
        }

        if (false === file_put_contents($localPathname, $stream)) {
            throw new \LogicException(
                sprintf('Unable to download the file "%s" from the filesystem.', $file->getPathname())
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
