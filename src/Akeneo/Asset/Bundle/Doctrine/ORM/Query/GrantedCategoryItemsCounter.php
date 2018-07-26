<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Granted category item counter
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedCategoryItemsCounter implements CategoryItemsCounterInterface
{
    /** @var ItemCategoryRepositoryInterface */
    private $itemRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepo;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param ItemCategoryRepositoryInterface $itemRepository       Item repository
     * @param CategoryRepositoryInterface     $categoryRepository   Category repository
     * @param CategoryAccessRepository        $categoryAccessRepo   Category Access repository
     * @param AuthorizationCheckerInterface   $authorizationChecker Authorization checker
     * @param TokenStorageInterface           $tokenStorage         Token storage
     */
    public function __construct(
        ItemCategoryRepositoryInterface $itemRepository,
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @see getItemsCountInCategory same logic with applying permissions
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
            return 0;
        }

        if ($inChildren) {
            $categoryIds = $this->categoryAccessRepo->getGrantedChildrenIds(
                $category,
                $this->tokenStorage->getToken()->getUser(),
                Attributes::VIEW_ITEMS
            );
        } else {
            $categoryIds = [$category->getId()];
        }

        return $this->itemRepository->getItemsCountInCategory($categoryIds);
    }
}
