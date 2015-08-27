<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Schedule previously uploaded files for processing
 * - read uploaded files
 * - move them to schedule directory where they will be collected by the processor
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class Scheduler implements SchedulerInterface
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param UploadCheckerInterface $uploadChecker
     * @param RawFileStorerInterface $rawFileStorer
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->uploadChecker = $uploadChecker;
        $this->rawFileStorer = $rawFileStorer;
    }

    /**
     * {@inheritdoc}
     *
     * - check uploaded files
     * - Move files from tmp uploaded storage to tmp scheduled storage
     */
    public function schedule(UploadContext $uploadContext)
    {
        $files             = [];
        $fileSystem        = new Filesystem();
        $uploadDirectory   = $uploadContext->getTemporaryUploadDirectory();
        $scheduleDirectory = $uploadContext->getTemporaryScheduleDirectory();

        $storedFiles = array_diff(scandir($uploadDirectory), ['.', '..']);

        if (!is_dir($scheduleDirectory)) {
            $fileSystem->mkdir($scheduleDirectory);
        }

        foreach ($storedFiles as $file) {
            $result = [
                'file'  => $file,
                'error' => null,
            ];
            if (!$this->isValidScheduledFilename($storedFiles, $file)) {
                $result['error'] = UploadStatus::STATUS_ERROR_CONFLICTS;
                $files[]         = $result;
                continue;
            }
            $filepath = $uploadDirectory . DIRECTORY_SEPARATOR . $file;
            $newPath  = $scheduleDirectory . DIRECTORY_SEPARATOR . $file;
            $fileSystem->rename($filepath, $newPath);
            $files[] = $result;
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledFiles(UploadContext $uploadContext)
    {
        $scheduleDir    = $uploadContext->getTemporaryScheduleDirectory();
        $scheduledFiles = [];
        if (is_dir($scheduleDir)) {
            $scheduledFiles = array_diff(scandir($scheduleDir), ['.', '..']);
            $scheduledFiles = array_map(function ($filename) use ($scheduleDir) {
                return new \SplFileInfo($scheduleDir . DIRECTORY_SEPARATOR . $filename);
            }, $scheduledFiles);
        }

        return $scheduledFiles;
    }

    /**
     * Check for valid filename :
     * - code must be unique if not localized
     * - two file with the same code, one localized, one not are invalid
     *
     * @param string[] $storedFiles
     * @param string   $filenameToCheck
     *
     * @return bool
     */
    protected function isValidScheduledFilename(array $storedFiles, $filenameToCheck)
    {
        $otherFilenames = array_diff($storedFiles, [$filenameToCheck]);

        $checkedFilenameInfos = $this->uploadChecker->parseFilename($filenameToCheck);
        $checkedIsLocalized   = null !== $checkedFilenameInfos['locale'];

        $filenamesIterator = new \ArrayIterator($otherFilenames);

        while ($filenamesIterator->valid()) {
            $filename = $filenamesIterator->current();

            $comparedInfos       = $this->uploadChecker->parseFilename($filename);
            $comparedIsLocalized = null !== $comparedInfos['locale'];

            if ($checkedFilenameInfos['code'] === $comparedInfos['code']
                && $checkedIsLocalized !== $comparedIsLocalized
            ) {
                return false;
            }
            $filenamesIterator->next();
        }

        return true;
    }
}
