<?php

declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordHydrator;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ValueHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class RecordHydratorSpec extends ObjectBehavior
{
    public function let(ValueHydratorInterface $valueHydrator, Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $valueHydrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordHydrator::class);
    }

    public function it_hydrates_a_record(
        $valueHydrator,
        TextAttribute $gameDescription,
        ImageAttribute $gameBoxImage
    ) {
        $labels = [
            'en_US' => 'World of Warcraft',
            'fr_FR' => 'World of Warcraft',
        ];
        $indexedAttributes = [
            'description_game_finger' => $gameDescription,
            'boximage_game_finger'    => $gameBoxImage,
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('description_game_fingerprint-fr_FR'),
            ValueKey::createFromNormalized('description_game_fingerprint-en_US'),
            ValueKey::createFromNormalized('boximage_game_fingerprint-mobile'),
        ]);
        $record = $this->hydrate(
            [
                'identifier'                 => 'wow_game_A8E76F8A76E87F6A',
                'code'                       => 'world_of_warcraft',
                'enriched_entity_identifier' => 'game',
                'labels'                     => json_encode($labels),
                'value_collection'           => json_encode([]),
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $valueHydrator->hydrate()->shouldNotBeCalled();
        $record->getIdentifier()->shouldBeAnInstanceOf(RecordIdentifier::class);
        $record->getEnrichedEntityIdentifier()->shouldBeAnInstanceOf(EnrichedEntityIdentifier::class);
        $record->getCode()->shouldBeAnInstanceOf(RecordCode::class);
        $record->getLabel('fr_FR')->shouldReturn('World of Warcraft');
        $record->getLabel('en_US')->shouldReturn('World of Warcraft');
    }

    public function it_hydrates_a_record_with_values(
        $valueHydrator,
        TextAttribute $gameDescription,
        ImageAttribute $gameBoxImage,
        AttributeIdentifier $gameDescriptionIdentifier,
        AttributeIdentifier $gameBoxImageIdentifier,
        Value $gameDescriptionFrFr,
        Value $gameDescriptionEnUS,
        Value $gameBoxImageMobile
    ) {
        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $gameDescriptionEnUSNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'en_US',
            'data'      => 'The famous MMORPG PC Game by Blizzard',
        ];
        $gameBoxImageMobileNormalized = [
            'attribute' => 'boximage_game_fingerprint',
            'channel'   => 'mobile',
            'locale'    => null,
            'data'      => [
                'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                'original_filename' => 'box_wow.png',
            ],
        ];
        $gameDescriptionFrFr->normalize()->willReturn($gameDescriptionFrFrNormalized);
        $gameDescriptionEnUS->normalize()->willReturn($gameDescriptionEnUSNormalized);
        $gameBoxImageMobile->normalize()->willReturn($gameBoxImageMobileNormalized);

        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $gameBoxImageIdentifier->normalize()->willReturn('boximage_game_fingerprint');
        $gameBoxImage->getIdentifier()->willReturn($gameBoxImageIdentifier);

        $rawValues = [
            'description_game_finger-fr_FR' => $gameDescriptionFrFrNormalized,
            'description_game_finger-en_US' => $gameDescriptionEnUSNormalized,
            'boximage_game_fingerprint-mobile' => $gameBoxImageMobileNormalized,
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('description_game_finger-fr_FR'),
            ValueKey::createFromNormalized('description_game_finger-en_US'),
            ValueKey::createFromNormalized('boximage_game_fingerprint-mobile'),
        ]);
        $indexedAttributes = [
            'description_game_fingerprint' => $gameDescription,
            'boximage_game_fingerprint' => $gameBoxImage,
        ];

        $valueHydrator->hydrate($gameDescriptionFrFrNormalized, $gameDescription)->willReturn($gameDescriptionFrFr);
        $valueHydrator->hydrate($gameDescriptionEnUSNormalized, $gameDescription)->willReturn($gameDescriptionEnUS);
        $valueHydrator->hydrate($gameBoxImageMobileNormalized, $gameBoxImage)->willReturn($gameBoxImageMobile);

        $record = $this->hydrate(
            [
                'identifier' => 'wow_game_A8E76F8A76E87F6A',
                'code' => 'world_of_warcraft',
                'enriched_entity_identifier' => 'game',
                'labels' => json_encode([]),
                'value_collection' => json_encode($rawValues),
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $record->getValues()->normalize()->shouldReturn([
                'description_game_finger-fr_FR'    => $gameDescriptionFrFrNormalized,
                'description_game_finger-en_US'    => $gameDescriptionEnUSNormalized,
                'boximage_game_fingerprint-mobile' => $gameBoxImageMobileNormalized,
            ]
        );
    }

    public function it_does_not_hydrate_unexpected_values(
        $valueHydrator,
        TextAttribute $gameDescription,
        AttributeIdentifier $gameDescriptionIdentifier,
        Value $gameDescriptionFrFr
    ) {
        $gameDescriptionFrFrNormalized = [
            'attribute' => 'description_game_fingerprint',
            'channel'   => null,
            'locale'    => 'fr_FR',
            'data'      => 'Le fameux MMORPG PC de Blizzard',
        ];
        $gameDescriptionFrFr->normalize()->willReturn($gameDescriptionFrFrNormalized);

        $gameDescriptionIdentifier->normalize()->willReturn('description_game_fingerprint');
        $gameDescription->getIdentifier()->willReturn($gameDescriptionIdentifier);

        $rawValues = [
            'description_game_finger-fr_FR'    => $gameDescriptionFrFrNormalized,
            'unknown_attribute1-fingerprint'    => [
                'attribute' => 'description_game_fingerprint',
                'channel'   => null,
                'locale'    => 'en_US',
                'data'      => 'The famous MMORPG PC Game by Blizzard',
            ],
            'unknown_attribute2-fingerprint' => [
                'attribute' => 'boximage_game_fingerprint',
                'channel'   => 'mobile',
                'locale'    => null,
                'data'      => [
                    'file_key'          => 'A8EF76A87E68768FA768AE76F876',
                    'original_filename' => 'box_wow.png',
                ],
            ],
        ];
        $expectedValueKeys = ValueKeyCollection::fromValueKeys([
            ValueKey::createFromNormalized('description_game_finger-fr_FR'),
        ]);
        $indexedAttributes = ['description_game_fingerprint' => $gameDescription];

        $valueHydrator->hydrate($gameDescriptionFrFrNormalized, $gameDescription)->willReturn($gameDescriptionFrFr);
        $record = $this->hydrate(
            [
                'identifier'                 => 'wow_game_A8E76F8A76E87F6A',
                'code'                       => 'world_of_warcraft',
                'enriched_entity_identifier' => 'game',
                'labels'                     => json_encode([]),
                'value_collection'           => json_encode($rawValues),
            ],
            $expectedValueKeys,
            $indexedAttributes
        );

        $record->getValues()->normalize()->shouldReturn([
            'description_game_finger-fr_FR' => $gameDescriptionFrFrNormalized,
        ]);
    }
}
