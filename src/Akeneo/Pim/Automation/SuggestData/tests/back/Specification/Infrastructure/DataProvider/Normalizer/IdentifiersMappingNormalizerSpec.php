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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMappingNormalizer::class);
    }

    public function it_normalizes_identifiers_mapping(
        IdentifiersMapping $mapping,
        AttributeInterface $attributeSku,
        AttributeInterface $attributeBrand,
        AttributeTranslationInterface $brandEN,
        AttributeTranslationInterface $brandFR,
        ArrayCollection $brandTranslations,
        \ArrayIterator $brandTransIterator,
        ArrayCollection $skuTranslations
    ): void {
        $mapping->getIdentifiers()->willReturn(
            [
                'mpn' => $attributeSku,
                'brand' => $attributeBrand,
                'ean' => null,
            ]
        );
        $attributeSku->getCode()->willReturn('sku');
        $attributeSku->getTranslations()->willReturn($skuTranslations);

        $skuTranslations->isEmpty()->willReturn(true);

        $attributeBrand->getCode()->willReturn('brand_code');
        $attributeBrand->getTranslations()->willReturn($brandTranslations);

        $brandTranslations->isEmpty()->willReturn(false);
        $brandTranslations->getIterator()->willReturn($brandTransIterator);
        $brandTransIterator->valid()->willReturn(true, true, false);
        $brandTransIterator->current()->willReturn($brandFR, $brandEN);
        $brandTransIterator->next()->shouldBeCalled();
        $brandTransIterator->rewind()->shouldBeCalled();

        $brandFR->getLocale()->willReturn('fr_FR');
        $brandFR->getLabel()->willReturn('Marque');

        $brandEN->getLocale()->willReturn('en_US');
        $brandEN->getLabel()->willReturn('Brand');

        $this->normalize($mapping)->shouldReturn(
            [
                [
                    'from' => ['id' => 'mpn'],
                    'to' => [
                        'id' => 'sku',
                        'label' => [],
                    ],
                ],
                [
                    'from' => ['id' => 'brand'],
                    'to' => [
                        'id' => 'brand_code',
                        'label' => [
                            'fr_FR' => 'Marque',
                            'en_US' => 'Brand',
                        ],
                    ],
                ],
            ]
        );
    }
}
