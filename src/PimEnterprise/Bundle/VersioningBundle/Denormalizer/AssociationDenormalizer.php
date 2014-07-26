<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AssociationDenormalizer extends AbstractEntityDenormalizer
{
    /** @var string */
    protected $productClass;

    /** @var string */
    protected $groupClass;

    /**
     * @param ManagerRegistry $registry
     * @param string          $entityClass
     * @param string          $productClass
     * @param string          $groupClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass, $productClass, $groupClass)
    {
        parent::__construct($registry, $entityClass);

        $this->productClass = $productClass;
        $this->groupClass   = $groupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($context['entity']) && null !== $context['entity']) {
            $association = $context['entity'];
        } else {
            $association = $this->createEntity();
            $association->setAssociationType(
                $this->getAssociationType($context['association_type_code'])
            );
        }

        if ('groups' === $context['part']) {
            if (strlen($data) > 0) {
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $group = $this->serializer->deserialize($identifier, $this->groupClass, $format);
                    $association->addGroup($group);
                }
            }
        } else {
            if (strlen($data) > 0) {
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $product = $this->serializer->deserialize($identifier, $this->productClass, $format);
                    $association->addProduct($product);
                }
            }
        }

        return $association;
    }

    /**
     * @param string $identifier
     *
     * @return AssociationType
     */
    protected function getAssociationType($identifier)
    {
        return $this->getAssociationTypeRepository()->findByReference($identifier);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository
     */
    protected function getAssociationTypeRepository()
    {
        return $this->managerRegistry->getRepository($this->associationTypeClass);
    }
}
