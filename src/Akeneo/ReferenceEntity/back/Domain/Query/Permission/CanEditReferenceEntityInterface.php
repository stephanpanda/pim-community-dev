<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Permission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface CanEditReferenceEntityInterface
{
    public function __invoke(UserIdentifier $userIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool;
}
