<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

/**
 * Proposition ODM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionRepository extends DocumentRepository implements PropositionRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p');
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder $qb
     */
    public function applyDatagridContext($qb, $productId)
    {
        $qb->field('product.$id')->equals(new \MongoId($productId));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function applyFilter($qb, $field, $operator, $value)
    {
        if ('IN' === $operator) {
            if (!empty($value)) {
                $qb->field($field)->in($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function applySorter($qb, $field, $direction)
    {
        $qb->sort($field, $direction);
    }
}
