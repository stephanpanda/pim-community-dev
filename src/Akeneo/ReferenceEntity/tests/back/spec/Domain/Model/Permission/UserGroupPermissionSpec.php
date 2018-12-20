<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use PhpSpec\ObjectBehavior;

class UserGroupPermissionSpec extends ObjectBehavior
{
    function it_normalizes_itself()
    {
        $this->beConstructedThrough('create', [
            UserGroupIdentifier::fromInteger(25),
            RightLevel::fromString('view')
        ]);

        $this->normalize()->shouldReturn([
            'user_group_identifier' => 25,
            'right_level' => 'view',
        ]);
    }
}
