<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer as PimProductPdfRenderer;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /**
     * @param EngineInterface           $templating
     * @param PdfBuilderInterface       $pdfBuilder
     * @param FilterProductValuesHelper $filterHelper
     * @param DataManager               $dataManager
     * @param CacheManager              $cacheManager
     * @param FilterManager             $filterManager
     * @param string                    $template
     * @param string                    $uploadDirectory
     * @param string|null               $customFont
     */
    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        $template,
        $uploadDirectory,
        $customFont = null
    ) {
        parent::__construct(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $template,
            $uploadDirectory,
            $customFont
        );

        $this->filterHelper = $filterHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $locale)
    {
        $values = $this->filterHelper->filter($product->getValues()->toArray(), $locale);
        $attributes = [];

        foreach ($values as $value) {
            $attributes[$value->getAttribute()->getCode()] = $value->getAttribute();
        }

        return $attributes;
    }

    /**
     * Adds attributes with 'pim_assets_collection' type to display images in header.
     *
     * {@inheritdoc}
     */
    protected function getImageAttributes(ProductInterface $product, $locale, $scope)
    {
        $attributes = parent::getImageAttributes($product, $locale, $scope);

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if (AttributeTypes::ASSETS_COLLECTION === $attribute->getAttributeType()) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }
}