<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processes mass uploaded asset files.
 *
 * For a given username, it:
 * - reads all files uploaded from the front end,
 * - creates the corresponding assets,
 * - adds them in the asset collection of a product or product model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionProcessor
{
    /** @var ImporterInterface */
    protected $importer;

    /** @var BuildAsset */
    protected $buildAsset;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var RetrieveAssetGenerationErrors */
    protected $retrieveAssetGenerationErrors;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var AddAssetToEntityWithValues */
    protected $addAssetToEntityWithValues;

    /**
     * @param ImporterInterface             $importer
     * @param BuildAsset                    $buildAsset
     * @param SaverInterface                $assetSaver
     * @param EventDispatcherInterface      $eventDispatcher
     * @param RetrieveAssetGenerationErrors $assetGenerationErrors
     * @param ObjectDetacherInterface       $objectDetacher
     * @param AddAssetToEntityWithValues    $addAssetToEntityWithValues
     */
    public function __construct(
        ImporterInterface $importer,
        BuildAsset $buildAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $assetGenerationErrors,
        ObjectDetacherInterface $objectDetacher,
        AddAssetToEntityWithValues $addAssetToEntityWithValues
    ) {
        $this->importer = $importer;
        $this->buildAsset = $buildAsset;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->retrieveAssetGenerationErrors = $assetGenerationErrors;
        $this->objectDetacher = $objectDetacher;
        $this->addAssetToEntityWithValues = $addAssetToEntityWithValues;
    }

    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext         $uploadContext
     * @param EntityToAddAssetsInto $addAssetsTo
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload(UploadContext $uploadContext, EntityToAddAssetsInto $addAssetsTo): ProcessedItemList
    {
        $processedItems = new ProcessedItemList();
        $importedFiles = $this->importer->getImportedFiles($uploadContext);

        $importedAssetCodes = [];
        foreach ($importedFiles as $file) {
            try {
                $asset = $this->buildAsset->fromFile($file);
                $reason = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;

                $this->assetSaver->save($asset);

                $event = $this->eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, new AssetEvent($asset));
                $errors = $this->retrieveAssetGenerationErrors->fromEvent($event);

                if (count($errors) > 0) {
                    $processedItems->addItem($file, ProcessedItem::STATE_SKIPPED, implode(PHP_EOL, $errors));
                } else {
                    $processedItems->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
                }
            } catch (\Exception $e) {
                $processedItems->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            } finally {
                if (isset($asset)) {
                    $importedAssetCodes[] = $asset->getCode();
                    $this->objectDetacher->detach($asset);
                }
            }
        }

        if (!empty($importedAssetCodes)) {
            try {
                $this->addAssetToEntityWithValues->add(
                    $addAssetsTo->getEntityId(),
                    $addAssetsTo->getAttributeCode(),
                    $importedAssetCodes
                );
            } catch (\InvalidArgumentException $e) {
                $processedItems->addItem($addAssetsTo, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            }
        }

        return $processedItems;
    }
}
