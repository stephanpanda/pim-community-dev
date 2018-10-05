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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;

/**
 * Prepare AttributesMapping model from Domain layer in order to be used by PIM.ai client.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /** @var string[] */
    public const PIM_AI_MAPPING_STATUS = [
        DomainAttributeMapping::ATTRIBUTE_PENDING => AttributeMapping::STATUS_PENDING,
        DomainAttributeMapping::ATTRIBUTE_MAPPED => AttributeMapping::STATUS_ACTIVE,
        DomainAttributeMapping::ATTRIBUTE_UNMAPPED => AttributeMapping::STATUS_INACTIVE,
    ];

    /**
     * @param AttributeMapping[] $attributesMapping
     *
     * @return array
     */
    public function normalize(array $attributesMapping): array
    {
        $result = [];
        foreach ($attributesMapping as $attributeMapping) {
            $attribute = $attributeMapping->getAttribute();

            $normalizedAttribute = null;
            if (null !== $attribute) {
                $attribute->setLocale('en_US');
                $normalizedAttribute = [
                    'id' => $attribute->getCode(),
                    'label' => [
                        'en_US' => $attribute->getLabel(),
                    ],
                    'type' => 'text', // TODO: Should be managed in APAI-174
                ];
            }

            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $normalizedAttribute,
                'status' => static::PIM_AI_MAPPING_STATUS[$attributeMapping->getStatus()],
            ];
        }

        return $result;
    }
}