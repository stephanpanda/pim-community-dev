<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity;

use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

/**
 * Abstract implementation of the item category access interface
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AbstractItemCategoryAccess implements CategoryAccessInterface
{
    /** @var int */
    protected $id;

    /** @var CategoryInterface */
    protected $category;

    /** @var GroupInterface */
    protected $userGroup;

    /** @var bool */
    protected $viewItems;

    /** @var bool */
    protected $editItems;

    /** @var bool */
    protected $ownItems;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserGroup(GroupInterface $group)
    {
        $this->userGroup = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditItems($editItems)
    {
        $this->editItems = $editItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditItems()
    {
        return $this->editItems;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewItems($viewItems)
    {
        $this->viewItems = $viewItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewItems()
    {
        return $this->viewItems;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnItems($ownItems)
    {
        $this->ownItems = $ownItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnItems()
    {
        return $this->ownItems;
    }
}
