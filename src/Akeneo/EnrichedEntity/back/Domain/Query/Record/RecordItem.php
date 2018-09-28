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

namespace Akeneo\EnrichedEntity\Domain\Query\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

/**
 * Read model representing a record within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordItem
{
    private const IDENTIFIER = 'identifier';
    private const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const IMAGE = 'image';

    /** @var RecordIdentifier */
    public $identifier;

    /** @var EnrichedEntityIdentifier */
    public $enrichedEntityIdentifier;

    /** @var RecordCode */
    public $code;

    /** @var LabelCollection */
    public $labels;

    /** @var Image|null */
    public $image;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                 => $this->identifier->normalize(),
            self::ENRICHED_ENTITY_IDENTIFIER => (string) $this->enrichedEntityIdentifier,
            self::CODE                       => (string) $this->code,
            self::LABELS                     => $this->labels->normalize(),
            self::IMAGE                      => $this->image->normalize(),
        ];
    }
}
