<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

/**
 * Checks whether a given record already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIdentifierShouldBeUnique extends Constraint
{
    public const ERROR_MESSAGE = 'pim_enriched_entity.record.validation.identifier.should_be_unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.record.record_is_unique';
    }
}