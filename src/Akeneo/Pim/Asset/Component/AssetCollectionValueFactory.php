<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Asset\Component;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Copy of the {@see Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataCollectionValueFactory} but without
 * sorting data.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ProductValueFactory.
 *
 * @author    Julien Janvier (j.janvier@gmail.com)
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetCollectionValueFactory implements ValueFactoryInterface
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /** @var string */
    protected $productValueClass;

    /**
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     * @param string                                   $productValueClass
     */
    public function __construct(
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        $productValueClass
    ) {
        $this->repositoryResolver = $repositoryResolver;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data, bool $ignoreUnknownData = false)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getReferenceDataCollection($attribute, $data, $ignoreUnknownData)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === AttributeTypes::ASSETS_COLLECTION;
    }

    /**
     * Checks if data is valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Gets a collection of reference data from an array of codes.
     *
     * @param AttributeInterface $attribute
     * @param array              $referenceDataCodes
     *
     * @return array
     */
    protected function getReferenceDataCollection(AttributeInterface $attribute, array $referenceDataCodes, bool $ignoreUnknownData)
    {
        $collection = [];

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($referenceDataCodes as $referenceDataCode) {
            $referenceData = $this->getReferenceData($attribute, $repository, $referenceDataCode, $ignoreUnknownData);
            if (null !== $referenceData && !in_array($referenceData, $collection, true)) {
                $collection[] = $referenceData;
            }
        }

        return $collection;
    }

    /**
     * Finds a reference data by code.
     *
     * @todo TIP-684: When deleting one element of the collection, we will end up throwing the exception.
     *       Problem is, when loading a product value from single storage, it will be skipped because of
     *       one reference data, when the others in the collection could be valid. So the value will not
     *       be loaded at all, when what we want is the value to be loaded minus the wrong reference data.
     *
     * @param AttributeInterface               $attribute
     * @param ReferenceDataRepositoryInterface $repository
     * @param string                           $referenceDataCode
     * @param bool                             $ignoreUnknownData
     *
     * @return ReferenceDataInterface
     */
    protected function getReferenceData(
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $repository,
        $referenceDataCode,
        bool $ignoreUnknownData
    ) {
        $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

        if (null === $referenceData && false === $ignoreUnknownData) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'reference data code',
                sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                static::class,
                $referenceDataCode
            );
        }

        return $referenceData;
    }
}
