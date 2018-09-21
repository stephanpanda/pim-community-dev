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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
interface RecordDetailsHydratorInterface
{
    public function hydrate(array $row, array $emptyValues): RecordDetails;
}