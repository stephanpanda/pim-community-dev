<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\AttributePermissionRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\AttributePermissionRepositoryInterface;

class AttributePermissionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('AttributeGroupAccesses')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'AttributeGroupAccesses');
    }

    function it_is_attribute_permission_requirement()
    {
        $this->shouldImplement(AttributePermissionRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributePermissionRepository::class);
    }
}
