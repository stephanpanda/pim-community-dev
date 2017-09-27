<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\Catalog\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;

class CategoryAccessManagerSpec extends ObjectBehavior
{
    function let(
        CategoryAccessRepository $accessRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepository $groupRepository,
        BulkSaverInterface $accessSaver,
        BulkRemoverInterface $accessRemover
    ) {
        $accessClass = 'PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess';
        $this->beConstructedWith(
            $accessRepository,
            $categoryRepository,
            $groupRepository,
            $accessSaver,
            $accessRemover,
            $accessClass
        );
    }

    function it_provides_user_groups_that_have_access_to_a_category(CategoryInterface $category, $accessRepository)
    {
        $accessRepository->getGrantedUserGroups($category, Attributes::VIEW_ITEMS)->willReturn(['foo', 'bar']);
        $accessRepository->getGrantedUserGroups($category, Attributes::EDIT_ITEMS)->willReturn(['bar']);
        $accessRepository->getGrantedUserGroups($category, Attributes::OWN_PRODUCTS)->willReturn(['bar']);

        $this->getViewUserGroups($category)->shouldReturn(['foo', 'bar']);
        $this->getEditUserGroups($category)->shouldReturn(['bar']);
        $this->getOwnUserGroups($category)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_category_for_the_provided_user_groups(
        CategoryInterface $category,
        $accessRepository,
        $accessSaver,
        Group $user,
        Group $admin
    ) {
        $category->getId()->willReturn(1);
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessRepository->revokeAccess($category, [$admin, $user])->shouldBeCalled();
        $accessSaver->saveAll(Argument::any())->shouldBeCalled();
        $this->setAccess($category, [$user, $admin], [$admin], [$admin], true);
    }

    function it_grants_access_on_a_category_for_the_provided_user_groups_and_does_not_flush(
        CategoryInterface $category,
        $accessRepository,
        $accessSaver,
        Group $user,
        Group $admin
    ) {
        $category->getId()->willReturn(1);
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessRepository->revokeAccess($category, [$admin, $user])->shouldBeCalled();
        $accessSaver->saveAll(Argument::any())->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin], [$admin], false);
    }

    function it_grants_access_on_a_new_category_for_the_provided_user_groups(
        CategoryInterface $category,
        $accessRepository,
        $accessSaver,
        Group $user,
        Group $admin
    ) {
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessSaver->saveAll(Argument::any())->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin], [$admin], true);
    }

    function it_adds_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo,
        $categoryRepository,
        $accessRepository,
        $accessSaver,
        Group $user
    ) {
        $addViewGroups = [$user];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [];
        $removeEditGroups = [];
        $removeOwnGroups = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn([]);

        $categoryRepository->findBy(['id' => $childrenIds])->willReturn([$childOne, $childTwo]);
        $accessSaver->saveAll(Argument::any())->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }

    function it_updates_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        ProductCategoryAccess $accessOne,
        ProductCategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Group $user,
        $accessSaver
    ) {
        $addViewGroups = [$user];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [];
        $removeEditGroups = [];
        $removeOwnGroups = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn($childrenIds);

        $accessRepository
            ->findBy(['category' => $childrenIds, 'userGroup' => $user])
            ->willReturn([$accessOne, $accessTwo]);

        $accessOne->setViewItems(true)->shouldBeCalled();
        $accessTwo->setViewItems(true)->shouldBeCalled();
        $accessSaver->saveAll(Argument::any())->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }

    function it_removes_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        ProductCategoryAccess $accessOne,
        ProductCategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Group $manager,
        $accessRemover
    ) {
        $addViewGroups = [];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [$manager];
        $removeEditGroups = [$manager];
        $removeOwnGroups = [$manager];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$manager], $childrenIds)->willReturn($childrenIds);

        $accessRepository
            ->findBy(['category' => $childrenIds, 'userGroup' => $manager])
            ->willReturn([$accessOne, $accessTwo]);
        $accessRemover->removeAll([$accessOne, $accessTwo])->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }
}