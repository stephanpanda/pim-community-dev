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

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAttributeRepository implements AttributeRepositoryInterface
{
    /** @var AbstractAttribute[] */
    private $attributes = [];

    public function create(AbstractAttribute $attribute): void
    {
        $key = $this->getKey($attribute->getIdentifier());
        if (isset($this->attributes[$key])) {
            throw new \RuntimeException('Attribute already exists');
        }

        $attributesForEntity = $this->findByEnrichedEntity($attribute->getEnrichedEntityIdentifier());
        foreach ($attributesForEntity as $attributeForEntity) {
            if ($attribute->getOrder()->equals($attributeForEntity->getOrder())) {
                throw new \Exception('An attribute already has this order for this enriched entity');
            }
        }

        $this->attributes[$key] = $attribute;
    }

    public function update(AbstractAttribute $attribute): void
    {
        $key = $this->getKey($attribute->getIdentifier());
        if (!isset($this->attributes[$key])) {
            throw new \RuntimeException('Expected to update one attribute, but none was updated');
        }
        $this->attributes[$key] = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        $key = $this->getKey($identifier);
        $attribute = $this->attributes[$key] ?? null;
        if (null === $attribute) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function findByEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getEnrichedEntityIdentifier()->equals($enrichedEntityIdentifier)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    public function getKey(AttributeIdentifier $identifier): string
    {
        return sprintf('%s_%s', $identifier->getEnrichedEntityIdentifier(), $identifier->getIdentifier());
    }

    /**
     * @return AbstractAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function deleteByIdentifier(AttributeIdentifier $identifier): void
    {
        $key = $this->getKey($identifier);
        $attribute = $this->attributes[$key] ?? null;
        if (null === $attribute) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        unset($this->attributes[$key]);
    }
}
