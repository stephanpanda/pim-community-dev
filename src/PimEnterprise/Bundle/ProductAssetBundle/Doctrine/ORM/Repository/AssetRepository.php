<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Product asset repository
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AssetRepository extends EntityRepository implements AssetRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($reference)
    {
        return $this->findOneBy(['code' => $reference]);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $selectDql = sprintf(
            '%s.id as id, CONCAT(\'[\', %s.code, \']\') as text',
            $this->getAlias(),
            $this->getAlias()
        );

        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->select($selectDql);

        if ($this->getClassMetadata()->hasField('sortOrder')) {
            $qb->orderBy(sprintf('%s.sortOrder', $this->getAlias()), 'DESC');
            $qb->addOrderBy(sprintf('%s.code', $this->getAlias()));
        } else {
            $qb->orderBy(sprintf('%s.code', $this->getAlias()));
        }

        if (null !== $search) {
            $searchDql = sprintf('%s.code LIKE :search', $this->getAlias());
            $qb->andWhere($searchDql)->setParameter('search', "%$search%");
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function findSimilarCodes($code)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb
            ->select(sprintf('%s.code', $this->getAlias()))
            ->andWhere(sprintf('%s.code LIKE :pattern', $this->getAlias()))
            ->orWhere(sprintf('%s.code = :code', $this->getAlias()))
            ->setParameters([
                ':pattern' => sprintf("%s_%s", $code, '%'),
                ':code'    => $code
            ]);

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function createAssetDatagridQueryBuilder(array $parameters = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select($this->getAlias())
            ->from($this->_entityName, $this->getAlias(), sprintf('%s.id', $this->getAlias()));

        // TODO: Filter by owned categories by the user

        return $qb;
    }

    /**
     * Apply tag filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param string       $operator
     * @param mixed        $value
     */
    public function applyTagFilter(QueryBuilder $qb, $field, $operator, $value)
    {
        $qb->leftJoin('pa.tags', 'tags');

        switch ($operator) {
            case Operators::IN_LIST:
                $this->applyFilterInList($qb, $field, $value);
                break;
            case Operators::IS_EMPTY:
                $this->applyFilterEmpty($qb, $field);
                break;
        }
    }

    /**
     * Find assets by identifiers
     *
     * @param array $identifiers
     *
     * @return ArrayCollection
     */
    public function findByIdentifiers(array $identifiers = [])
    {
        $qb = $this->createQueryBuilder($this->getAlias());

        return $qb->select($this->getAlias())
            ->where($this->getAlias() . '.code IN (:identifiers)')
            ->setParameter('identifiers', $identifiers)
            ->getQuery()
            ->getResult();
    }

    /**
     * Apply an in list filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param mixed        $value
     */
    protected function applyFilterInList(QueryBuilder $qb, $field, $value)
    {
        if (!empty($value)) {
            $qb->andWhere($qb->expr()->in($field, $value));
        }
    }

    /**
     * Apply a is_empty filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     */
    protected function applyFilterEmpty(QueryBuilder $qb, $field)
    {
        $qb->andWhere($qb->expr()->isNull($field));
    }

    /**
     * Alias of the repository
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'pa';
    }
}
