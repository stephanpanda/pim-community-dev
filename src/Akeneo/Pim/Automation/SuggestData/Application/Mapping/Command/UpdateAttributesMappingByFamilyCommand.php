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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommand
{
    /** @var string */
    private $familyCode;

    /** @var AttributeMapping[] */
    private $attributesMapping = [];

    /**
     * @param string $familyCode
     * @param array $mapping
     *
     * @throws InvalidMappingException
     */
    public function __construct(string $familyCode, array $mapping)
    {
        $this->attributesMapping = [];
        $this->validate($mapping);

        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }

    /**
     * @return AttributeMapping[]
     */
    public function getAttributesMapping(): array
    {
        return $this->attributesMapping;
    }

    /**
     * Validates data and creates AttributesMapping.
     *
     * Format is:
     * [
     *      "color" => [
     *          "pim_ai_attribute" => [
     *              "label" => "Color",
     *              "type" => "multiselect"
     *          ],
     *          "attribute" => "tshirt_style",
     *          "status" => 1
     *      ]
     * ]
     *
     * @param array $mapping
     *
     * @throws InvalidMappingException
     */
    private function validate(array $mapping): void
    {
        foreach ($mapping as $targetKey => $mappingRow) {
            if (!is_string($targetKey)) {
                throw InvalidMappingException::expectedTargetKey();
            }

            if (!array_key_exists('attribute', $mappingRow)) {
                throw InvalidMappingException::expectedKey($targetKey, 'attribute');
            }

            if (!array_key_exists('status', $mappingRow)) {
                throw InvalidMappingException::expectedKey($targetKey, 'status');
            }

            $this->attributesMapping[] =
                new AttributeMapping($targetKey, $mappingRow['status'], $mappingRow['attribute']);
        }
    }
}
