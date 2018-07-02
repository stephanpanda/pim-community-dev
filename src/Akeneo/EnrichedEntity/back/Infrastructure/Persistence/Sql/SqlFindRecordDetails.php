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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Query\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\back\Domain\Query\RecordDetails;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordIdentifier $recordIdentifier
    ): ?RecordDetails {
        $result = $this->fetchResult($enrichedEntityIdentifier, $recordIdentifier);

        if (empty($result)) {
            return null;
        }

        $recordDetails = $this->hydrateRecordDetails(
            $result['identifier'],
            $result['enriched_entity_identifier'],
            $result['labels']
        );

        return $recordDetails;
    }

    private function fetchResult(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordIdentifier $recordIdentifier
    ): array {
        $query = <<<SQL
        SELECT identifier, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE enriched_entity_identifier = :enriched_entity_identifier AND identifier = :record_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
            'record_identifier'          => (string) $recordIdentifier
        ]);
        $result = $statement->fetch();
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    private function hydrateRecordDetails(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $normalizedLabels
    ): RecordDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);

        $recordDetails = new RecordDetails();
        $recordDetails->identifier = RecordIdentifier::fromString($identifier);
        $recordDetails->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordDetails->labels = LabelCollection::fromArray($labels);

        return $recordDetails;
    }
}