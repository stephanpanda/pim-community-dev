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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindRecordDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordDetailsQuery = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_records()
    {
        $this->assertNull(($this->findRecordDetailsQuery)(
                RecordIdentifier::create('unknown_enriched_entity', 'unknown_record_identifier'),
                EnrichedEntityIdentifier::fromString('unknown_enriched_entity')
            )
        );
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $actualStarck = ($this->findRecordDetailsQuery)(
            RecordIdentifier::create('designer', 'starck'),
            EnrichedEntityIdentifier::fromString('designer')
        );

        $expectedStarck = new RecordDetails();
        $expectedStarck->identifier = RecordIdentifier::create('designer', 'starck');
        $expectedStarck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedStarck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $this->assertRecordDetails($expectedStarck, $actualStarck);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityAndRecords(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'starck'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'),
                ['fr_Fr' => 'Philippe Starck']
            )
        );
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'coco'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('coco'),
                ['fr_Fr' => 'Coco Chanel']
            )
        );
    }

    private function assertRecordDetails(RecordDetails $expected, RecordDetails $actual)
    {
        $this->assertEquals($expected->identifier, $actual->identifier);
        $this->assertEquals($expected->enrichedEntityIdentifier, $actual->enrichedEntityIdentifier);
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            )
        );
    }
}
