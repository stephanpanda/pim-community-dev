<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Normalizer\Flat;

use Akeneo\Asset\Component\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat asset category normalizer
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetCategoryNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $categoryNormalizer;

    /**
     * @param NormalizerInterface   $categoryNormalizer
     * @param CategoryAccessManager $categoryManager
     */
    public function __construct(NormalizerInterface $categoryNormalizer, CategoryAccessManager $categoryManager)
    {
        $this->categoryNormalizer = $categoryNormalizer;
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param CategoryInterface $category
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $normalizedCategory = $this->categoryNormalizer->normalize($category, $format, $context);

        $normalizedCategory['view_permission'] = implode(
            array_map('strval', $this->categoryManager->getViewUserGroups($category)),
            ','
        );
        $normalizedCategory['edit_permission'] = implode(
            array_map('strval', $this->categoryManager->getEditUserGroups($category)),
            ','
        );

        return $normalizedCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }
}