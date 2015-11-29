<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\ProductMediaInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAware;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface;

/**
 * Present a file value
 *
 * @author Filips Alpe <filips@akeneo.com>
 *
 * @deprecated since 1.4 should be removed in 1.5
 */
class FilePresenter implements ProductValuePresenterInterface, TwigAwareInterface
{
    use TwigAware;

    /** @staticvar string */
    const TEMPLATE = 'PimEnterpriseWorkflowBundle:ProductValue:file.html.twig';

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return AttributeTypes::FILE === $value->getAttribute()->getAttributeType()
            && $value->getData() instanceof ProductMediaInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function present(ProductValueInterface $value)
    {
        $filename = $value->getData()->getFilename();
        $title = $value->getData()->getOriginalFilename();

        if (null !== $filename && null !== $title) {
            return $this->twig->loadTemplate(static::TEMPLATE)->render(
                [
                    'filename' => $filename,
                    'title'    => $title
                ]
            );
        }
    }
}
