<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates and validates a asset
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetUpdater implements ObjectUpdaterInterface
{
    /** @var TagRepositoryInterface */
    protected $tagRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * @param TagRepositoryInterface      $tagRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AssetFactory                $assetFactory
     */
    public function __construct(
        TagRepositoryInterface $tagRepository,
        CategoryRepositoryInterface $categoryRepository,
        AssetFactory $assetFactory
    ) {
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->assetFactory = $assetFactory;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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

        foreach ($data as $field => $item) {
            $this->setData($asset, $field, $item);
        }

        return $this;
    }

    /**
     * @param AssetInterface $asset
     * @param string         $field
     * @param mixed          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(AssetInterface $asset, $field, $data)
    {
        switch ($field) {
            case 'tags':
                $this->setTags($asset, $data);
                break;
            case 'categories':
                $this->setCategories($asset, $data);
                break;
            case 'end_of_use':
                $this->validateDateFormat($data);
                $asset->setEndOfUseAt(new \DateTime($data));
                break;
            case 'localized':
                $this->setLocalized($asset, $data);
                break;
            default:
                $this->accessor->setValue($asset, $field, $data);
        }
    }

    /**
     * It sets the tags by diff with existing tags and then remove other tags (due to doctrine UOW that does not
     * update link between the tags and the asset).
     *
     * @param AssetInterface $asset
     * @param array          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setTags(AssetInterface $asset, array $data)
    {
        $newTags = $data;
        $tagCodes = $asset->getTagCodes();

        if (!empty($tagCodes)) {
            $newTags = array_diff($data, $tagCodes);
        }

        foreach ($newTags as $tagCode) {
            $asset->addTag($this->getTagByCode($tagCode));
        }

        if (!empty($tagCodes)) {
            $toRemoveTags = array_diff($tagCodes, $data);
            $this->removeTagsByCodes($asset, $toRemoveTags);
        }
    }

    /**
     * It sets the categories by diff with existing tags and then remove other categories (due to doctrine UOW that
     * does not update link between the categories and the asset).
     *
     * @param AssetInterface $asset
     * @param array          $data
     *
     * @throws InvalidPropertyException
     */
    protected function setCategories(AssetInterface $asset, array $data)
    {
        $newCategories = $data;
        $categoriesCode = $asset->getCategoryCodes();

        if (!empty($categoriesCode)) {
            $newCategories = array_diff($newCategories, $categoriesCode);
        }

        foreach ($newCategories as $categoryCode) {
            $asset->addCategory($this->getCategoryByCode($categoryCode));
        }

        if (!empty($categoriesCode)) {
            $categories = array_diff($categoriesCode, $data);
            $this->removeCategoriesByCodes($asset, $categories);
        }
    }

    /**
     * @param string $data
     *
     * @throws InvalidPropertyException
     */
    protected function validateDateFormat($data)
    {
        if (null === $data) {
            return;
        }

        try {
            new \DateTime($data);
        } catch (\Exception $e) {
            throw InvalidPropertyException::dateExpected(
                'end_of_use',
                'yyyy-mm-dd',
                'updater',
                'asset',
                $data
            );
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
            throw InvalidPropertyException::dateExpected(
                'end_of_use',
                'yyyy-mm-dd',
                'updater',
                'asset',
                $data
            );
        }
    }

    /**
     * @param string $tagCode
     *
     * @throws InvalidPropertyException
     *
     * @return TagInterface
     */
    protected function getTagByCode($tagCode)
    {
        $tag = $this->tagRepository->findOneByIdentifier($tagCode);

        if (null === $tag) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'tags',
                'tag code',
                'The tag does not exist',
                'updater',
                'asset',
                $tagCode
            );
        }

        return $tag;
    }

    /**
     * @param string $categoryCode
     *
     * @throws InvalidPropertyException
     *
     * @return CategoryInterface
     */
    protected function getCategoryByCode($categoryCode)
    {
        $category = $this->categoryRepository->findOneByIdentifier($categoryCode);

        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                'updater',
                'asset',
                $categoryCode
            );
        }

        return $category;
    }

    /**
     * @param AssetInterface $asset
     * @param array          $tags
     *
     * @throws InvalidPropertyException
     */
    protected function removeTagsByCodes(AssetInterface $asset, array $tags)
    {
        foreach ($tags as $tagCode) {
            $asset->removeTag($this->getTagByCode($tagCode));
        }
    }

    /**
     * @param AssetInterface $asset
     * @param array          $categories
     *
     * @throws InvalidPropertyException
     */
    protected function removeCategoriesByCodes(AssetInterface $asset, array $categories)
    {
        foreach ($categories as $categoryCode) {
            $asset->removeCategory($this->getCategoryByCode($categoryCode));
        }
    }

    /**
     * @param AssetInterface $asset
     * @param bool           $isLocalized
     */
    protected function setLocalized(AssetInterface $asset, $isLocalized)
    {
        $this->assetFactory->createReferences($asset, $isLocalized);
    }
}