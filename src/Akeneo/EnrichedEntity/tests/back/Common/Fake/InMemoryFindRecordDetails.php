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

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var RecordDetails[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(RecordDetails $recordDetails)
    {
        $this->results[(string) $recordDetails->identifier] = $recordDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        RecordIdentifier $recordIdentifier
    ): ?RecordDetails {
        return $this->results[(string) $recordIdentifier] ?? null;
    }
}
