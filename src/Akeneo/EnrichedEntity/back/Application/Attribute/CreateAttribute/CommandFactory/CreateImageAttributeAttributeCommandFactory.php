<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateImageAttributeAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'image' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateImageAttributeCommand();
        $command->identifier = [
            'identifier' => $normalizedCommand['identifier']['identifier'],
            'enriched_entity_identifier' => $normalizedCommand['identifier']['enriched_entity_identifier']
        ];
        $command->code = $normalizedCommand['code'];
        $command->enrichedEntityIdentifier = $normalizedCommand['enriched_entity_identifier'];
        $command->labels = $normalizedCommand['labels'];
        $command->order = $normalizedCommand['order'];
        $command->required = $normalizedCommand['required'];
        $command->valuePerChannel = $normalizedCommand['value_per_channel'];
        $command->valuePerLocale = $normalizedCommand['value_per_locale'];
        $command->maxFileSize = $normalizedCommand['max_file_size'];
        $command->allowedExtensions = $normalizedCommand['allowed_extensions'] ?? [];

        return $command;
    }
}
