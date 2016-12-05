<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Repository\NativeSql;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectCompletenessRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessRepository implements ProjectCompletenessRepositoryInterface
{
    /** @var EntityManager */
    private $entityManger;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManger = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectCompleteness(ProjectInterface $project, $userId = null)
    {
        $query = $this->buildSqlQuery($userId);
        $parameters = $this->buildQueryParameters($project, $userId);

        return $this->entityManger->getConnection()->fetchAssoc($query, $parameters);
    }

    /**
     * Build your query parameters
     *
     * @param ProjectInterface $project
     * @param int|null         $userId
     *
     * @return array
     */
    private function buildQueryParameters(ProjectInterface $project, $userId = null)
    {
        $parameters = [
            'project_id' => $project->getId(),
            'channel_id' => $project->getChannel()->getId(),
            'locale_id' => $project->getLocale()->getId(),
        ];

        if (null !== $userId) {
            $parameters['user_id'] = $userId;
        }

        return $parameters;
    }

    /**
     * Build the project completeness query
     *
     * @param int|null $userId
     *
     * @return string
     */
    private function buildSqlQuery($userId = null)
    {
        $extraTableJoins = $extraConditions = '';

        if (null !== $userId) {
            $extraTableJoins = <<<SQL
INNER JOIN `pimee_security_attribute_group_access` AS `attribute_group_access`
    ON `attribute_group_access`.`attribute_group_id` = `completeness_per_attribute_group`.`attribute_group_id`
INNER JOIN `oro_user_access_group` AS `user_group`
    ON `user_group`.`group_id` = `attribute_group_access`.`user_group_id`
INNER JOIN `oro_user` AS `user`
    ON `user_group`.`user_id` = `user`.`id`
SQL;

            $extraConditions = <<<SQL
AND `user`.`id` = :user_id
SQL;
        }

        return <<<SQL
SELECT
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_in_progress` = 0 AND `attribute_group_done` = 0
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `todo`,
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_done` <> `total_attribute_group` AND `attribute_group_in_progress` > 0
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `in_progress`,
    COALESCE(
        SUM(
            CASE 
                WHEN `attribute_group_done` = `total_attribute_group`
                THEN 1 ELSE 0 
            END
        ),
        0
    ) AS `done`
FROM (
	SELECT 
		SUM(`completeness_per_attribute_group`.`has_at_least_one_required_attribute_filled`) AS `attribute_group_in_progress`,
		SUM(`completeness_per_attribute_group`.`is_complete`) AS `attribute_group_done`,
		COUNT(`project_product`.`product_id`) AS `total_attribute_group`
	FROM `akeneo_activity_manager_project` AS `project`
	INNER JOIN `akeneo_activity_manager_project_product` AS `project_product` 
		ON `project`.`id` = `project_product`.`project_id`
	INNER JOIN `akeneo_activity_manager_completeness_per_attribute_group` AS `completeness_per_attribute_group` 
		ON `project_product`.`product_id` = `completeness_per_attribute_group`.`product_id`
    $extraTableJoins
	WHERE `project`.`id` = :project_id
	AND `completeness_per_attribute_group`.`channel_id` = :channel_id 
	AND `completeness_per_attribute_group`.`locale_id` = :locale_id
	$extraConditions
	GROUP BY `project_product`.`product_id`
) `completeness`
SQL;
    }
}
