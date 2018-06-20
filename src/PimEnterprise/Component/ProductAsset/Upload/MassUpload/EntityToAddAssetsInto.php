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

/**
 * This DTO is used to add newly uploaded assets to an asset collection field of a product or product model.
 *
 * It contains the ID of the product/product model and the attribute code of the asset collection.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class EntityToAddAssetsInto
{
    /** @var int */
    private $entityId;

    /** @var string */
    private $attributeCode;

    /**
     * @param int    $entityId
     * @param string $attributeCode
     */
    public function __construct(int $entityId, string $attributeCode)
    {
        $this->entityId = $entityId;
        $this->attributeCode = $attributeCode;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
