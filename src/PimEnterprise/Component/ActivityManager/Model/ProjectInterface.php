<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return \DateTime
     */
    public function getDueDate();

    /**
     * @param \DateTime $dueDate
     *
     * @return
     */
    public function setDueDate(\DateTime $dueDate = null);

    /**
     * @return UserInterface
     */
    public function getOwner();

    /**
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner);

    /**
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @param LocaleInterface $locale
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * @return DatagridView
     */
    public function getDatagridView();

    /**
     * @param DatagridView $datagridView
     */
    public function setDatagridView($datagridView);

    /**
     * Add a new user group to the Project.
     *
     * @param Group $group
     */
    public function addUserGroup(Group $group);

    /**
     * Remove a user group to the Project.
     *
     * @param Group $group
     */
    public function removeUserGroup(Group $group);

    /**
     * Returns user groups.
     *
     * @return ArrayCollection $group
     */
    public function getUserGroups();

    /**
     * Returns PQB filters in json.
     *
     * @return array $productFilters
     */
    public function getProductFilters();

    /**
     * @param string $productFilters
     */
    public function setProductFilters($productFilters);
}
