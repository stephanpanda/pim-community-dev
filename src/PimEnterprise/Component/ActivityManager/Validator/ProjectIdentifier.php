<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectIdentifier extends Constraint
{
    /** @var string */
    public $message = 'The project "%s" doesn\'t exist.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'project_identifier_validator';
    }
}
