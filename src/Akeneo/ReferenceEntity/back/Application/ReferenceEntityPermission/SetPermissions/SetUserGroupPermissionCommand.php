<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions;

/**
 * This command represent a permission set for a user group
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SetUserGroupPermissionCommand
{
    /** @var int */
    public $userGroupIdentifier;

    /** @var string */
    public $rightLevel;
}