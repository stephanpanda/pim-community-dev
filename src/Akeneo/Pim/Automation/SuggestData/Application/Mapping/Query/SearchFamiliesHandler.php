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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\FamilySearchableRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandler
{
    /** @var FamilySearchableRepositoryInterface */
    private $familyRepository;

    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param FamilySearchableRepositoryInterface $familyRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        FamilySearchableRepositoryInterface $familyRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->familyRepository = $familyRepository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param SearchFamiliesQuery $getFamiliesQuery
     *
     * @return FamilyCollection
     */
    public function handle(SearchFamiliesQuery $getFamiliesQuery): FamilyCollection
    {
        $dataProvider = $this->dataProviderFactory->create();

        $families = $this->familyRepository->findBySearch(
            $getFamiliesQuery->getPage(),
            $getFamiliesQuery->getLimit(),
            $getFamiliesQuery->getSearch(),
            $getFamiliesQuery->getFamilyIdentifiers()
        );

        $familyCollection = new FamilyCollection();

        foreach ($families as $family) {
            $labels = [];
            foreach ($family->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $attributesMappingResponse = $dataProvider->getAttributesMapping($family->getCode());
            $familyCollection->add(
                new Family(
                    $family->getCode(),
                    $labels,
                    $this->getMappingStatus($attributesMappingResponse)
                )
            );
        }

        return $familyCollection;
    }

    /**
     * @param AttributesMappingResponse $attributesMappingResponse
     *
     * @return int
     */
    private function getMappingStatus(AttributesMappingResponse $attributesMappingResponse): int
    {
        $attributeStatuses = [];
        foreach ($attributesMappingResponse as $attributeMapping) {
            $attributeStatuses[] = $attributeMapping->getStatus();
        }

        if (empty($attributeStatuses)) {
            return Family::MAPPING_EMPTY;
        }

        if (in_array(AttributeMapping::ATTRIBUTE_PENDING, $attributeStatuses)) {
            return Family::MAPPING_PENDING;
        }

        return Family::MAPPING_FULL;
    }
}
