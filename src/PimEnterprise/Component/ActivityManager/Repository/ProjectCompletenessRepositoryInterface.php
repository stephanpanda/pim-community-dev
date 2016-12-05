<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Repository;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectCompletenessRepositoryInterface
{
    /**
     * Get the project completeness for a contributor.
     *
     * @param ProjectInterface $project
     * @param int|null         $userId
     *
     * @return array
     */
    public function getProjectCompleteness(ProjectInterface $project, $userId = null);
}
